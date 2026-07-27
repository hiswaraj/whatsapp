<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\WhatsappAccount;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    /**
     * Handle Meta Webhook subscription verification (GET challenge).
     */
    public function verifyChallenge(Request $request, ?string $verify_token = null): Response
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token) {
            if ($verify_token) {
                // Unique WABA webhook verification
                $waba = WhatsappAccount::where('verify_token', $verify_token)->first();
                if ($waba && $token === $waba->verify_token) {
                    return response($challenge, 200)->header('Content-Type', 'text/plain');
                }
            } else {
                // Global callback fallback
                $exists = WhatsappAccount::where('verify_token', $token)->exists();
                if ($exists || $token === config('services.meta.verify_token', 'default_verify_token')) {
                    return response($challenge, 200)->header('Content-Type', 'text/plain');
                }
            }
        }

        return response('Unauthorized challenge verification.', 403)->header('Content-Type', 'text/plain');
    }

    /**
     * Handle incoming Meta Webhook event updates (POST payloads).
     */
    public function handleWebhook(Request $request, ?string $verify_token = null): JsonResponse
    {
        Log::info('Meta Webhook Payload Received. Token: ' . ($verify_token ?? 'global'), $request->all());

        $waba = null;
        if ($verify_token) {
            $waba = WhatsappAccount::where('verify_token', $verify_token)->first();
        }

        $payload = $request->all();

        // Parse entries and changes
        if (!empty($payload['entry'])) {
            foreach ($payload['entry'] as $entry) {
                if (!empty($entry['changes'])) {
                    foreach ($entry['changes'] as $change) {
                        $value = $change['value'] ?? [];
                        
                        // Check for status updates
                        if (!empty($value['statuses'])) {
                            foreach ($value['statuses'] as $statusUpdate) {
                                $this->processMessageStatusUpdate($statusUpdate, $waba);
                            }
                        }

                        // Check for incoming customer messages
                        if (!empty($value['messages'])) {
                            $metadata = $value['metadata'] ?? [];
                            $contacts = $value['contacts'] ?? [];
                            foreach ($value['messages'] as $msgData) {
                                $this->processIncomingMessage($msgData, $metadata, $contacts, $waba);
                            }
                        }
                    }
                }
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Webhook processed successfully.']);
    }

    /**
     * Parse and process incoming customer text/button messages.
     */
    protected function processIncomingMessage(array $msgData, array $metadata, array $contactsData = [], ?WhatsappAccount $waba = null): void
    {
        $from = $msgData['from'] ?? null;
        $msgId = $msgData['id'] ?? null;
        $type = $msgData['type'] ?? 'text';
        $body = '';

        if ($type === 'text') {
            $body = $msgData['text']['body'] ?? '';
        } elseif ($type === 'button') {
            $body = $msgData['button']['text'] ?? '';
        } elseif ($type === 'interactive') {
            $body = $msgData['interactive']['button_reply']['title'] ?? 
                    $msgData['interactive']['list_reply']['title'] ?? '';
        }

        $phoneNumberId = $metadata['phone_number_id'] ?? null;

        if (!$from || !$msgId || !$phoneNumberId) {
            return;
        }

        try {
            if (!$waba || $waba->phone_number_id !== $phoneNumberId) {
                $wabaByPhone = WhatsappAccount::where('phone_number_id', $phoneNumberId)->first();
                if ($wabaByPhone) {
                    $waba = $wabaByPhone;
                }
            }

            if (!$waba) {
                Log::warning("Webhook incoming message ignored: No WABA found matching Phone Number ID {$phoneNumberId}");
                return;
            }

            $userId = $waba->user_id;

            // Resolve profile name
            $profileName = 'Guest';
            if (!empty($contactsData)) {
                foreach ($contactsData as $c) {
                    if (($c['wa_id'] ?? '') === $from) {
                        $profileName = $c['profile']['name'] ?? 'Guest';
                        break;
                    }
                }
            }

            // Clean number
            $fromCleaned = \App\Models\Contact::normalizePhoneNumber($from);

            // Find or create Contact using flexible phone number matching
            $contact = \App\Models\Contact::findByMobile($userId, $from);

            if (!$contact) {
                $contact = \App\Models\Contact::create([
                    'user_id' => $userId,
                    'name' => $profileName,
                    'mobile_number' => $fromCleaned
                ]);
            } else {
                // Update contact name if previous name was just the phone number or generic and Meta provides a real name
                if (($contact->name === $contact->mobile_number || $contact->name === $from || $contact->name === 'Guest') && !empty($profileName) && $profileName !== 'Guest') {
                    $contact->name = $profileName;
                }
                // Normalize stored mobile number to clean standard format
                $contact->mobile_number = $fromCleaned;
                $contact->save();
            }

            // Find or create Conversation
            $conversation = \App\Models\Conversation::firstOrCreate([
                'user_id' => $userId,
                'whatsapp_account_id' => $waba->id,
                'contact_id' => $contact->id
            ], [
                'last_message_at' => now(),
                'unread_count' => 0
            ]);

            // Save incoming message in database
            Message::create([
                'user_id' => $userId,
                'conversation_id' => $conversation->id,
                'whatsapp_account_id' => $waba->id,
                'meta_message_id' => $msgId,
                'type' => 'incoming',
                'message_type' => 'text',
                'body' => $body,
                'status' => 'read'
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'unread_count' => $conversation->unread_count + 1
            ]);

            // Run through chatbot Flow Executor engine!
            $executor = new \App\Services\FlowExecutorService();
            $executor->handleIncomingMessage($conversation, $body);

        } catch (Exception $e) {
            Log::error('Error processing incoming webhook message: ' . $e->getMessage());
        }
    }

    /**
     * Parse and apply status updates to local messages and campaigns.
     */
    protected function processMessageStatusUpdate(array $statusUpdate, ?WhatsappAccount $waba = null): void
    {
        $metaMessageId = $statusUpdate['id'] ?? null;
        $status = $statusUpdate['status'] ?? null; // sent, delivered, read, failed

        if (!$metaMessageId || !$status) {
            return;
        }

        try {
            $query = Message::where('meta_message_id', $metaMessageId);
            if ($waba) {
                $query->where('whatsapp_account_id', $waba->id);
            }
            $message = $query->first();

            if ($message) {
                $updateData = ['status' => $status];
                
                // Capture error message if failed
                if ($status === 'failed' && !empty($statusUpdate['errors'])) {
                    $updateData['error_message'] = $statusUpdate['errors'][0]['message'] ?? 'Meta Delivery Failure';
                }

                $message->update($updateData);

                // If linked to a campaign, trigger telemetry counts recalculation
                $campaign = $message->campaign;
                if ($campaign) {
                    $campaign->update([
                        'sent_count' => Message::where('campaign_id', $campaign->id)->whereIn('status', ['sent', 'delivered', 'read'])->count(),
                        'delivered_count' => Message::where('campaign_id', $campaign->id)->whereIn('status', ['delivered', 'read'])->count(),
                        'read_count' => Message::where('campaign_id', $campaign->id)->where('status', 'read')->count(),
                        'failed_count' => Message::where('campaign_id', $campaign->id)->where('status', 'failed')->count(),
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Error processing webhook message status update: ' . $e->getMessage());
        }
    }
}
