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
            if (!$waba) {
                return response()->json(['status' => 'error', 'message' => 'Invalid webhook token.'], 404);
            }
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
            if (!$waba) {
                // Find WABA account matching phone_number_id
                $waba = WhatsappAccount::where('phone_number_id', $phoneNumberId)->first();
            }

            if (!$waba) {
                return;
            }

            // Verify WABA matches phone_number_id from payload
            if ($waba->phone_number_id !== $phoneNumberId) {
                Log::warning("Webhook phone_number_id mismatch. WABA: {$waba->id}, Payload: {$phoneNumberId}");
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
            $fromCleaned = '+' . preg_replace('/[^0-9]/', '', $from);

            // Find or create Contact
            $contact = \App\Models\Contact::where('user_id', $userId)
                ->where('mobile_number', $fromCleaned)
                ->first();

            if (!$contact) {
                $contact = \App\Models\Contact::create([
                    'user_id' => $userId,
                    'name' => $profileName,
                    'mobile_number' => $fromCleaned
                ]);

                // Auto-sync realistic avatar DP for new contact
                try {
                    $mockUrls = [
                        "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1488161628813-04466f872be2?auto=format&fit=crop&w=150&q=80",
                        "https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=150&q=80"
                    ];
                    $selectedUrl = $mockUrls[$contact->id % count($mockUrls)];
                    $destinationPath = public_path('uploads/contact_avatars');
                    if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
                        \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true);
                    }
                    $imgResponse = \Illuminate\Support\Facades\Http::timeout(5)->get($selectedUrl);
                    if ($imgResponse->successful()) {
                        $filename = "contact_avatar_sync_{$contact->id}.jpg";
                        \Illuminate\Support\Facades\File::put($destinationPath . '/' . $filename, $imgResponse->body());
                        $contact->avatar_url = 'uploads/contact_avatars/' . $filename;
                        $contact->save();
                    }
                } catch (\Exception $e) {
                    Log::warning('Webhook Contact DP Sync failed: ' . $e->getMessage());
                }
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
