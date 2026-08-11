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
     * Parse and process incoming customer text/button/media messages.
     */
    protected function processIncomingMessage(array $msgData, array $metadata, array $contactsData = [], ?WhatsappAccount $waba = null): void
    {
        $from = $msgData['from'] ?? null;
        $msgId = $msgData['id'] ?? null;
        $type = $msgData['type'] ?? 'text';
        $body = '';
        $messageType = 'text';
        $mediaPath = null;
        $mediaMimeType = null;

        if ($type === 'text') {
            $messageType = 'text';
            $body = $msgData['text']['body'] ?? '';
        } elseif ($type === 'button') {
            $messageType = 'text';
            $body = $msgData['button']['text'] ?? '';
        } elseif ($type === 'interactive') {
            $messageType = 'text';
            $body = $msgData['interactive']['button_reply']['title'] ?? 
                    $msgData['interactive']['list_reply']['title'] ?? '';
        } elseif (in_array($type, ['image', 'video', 'audio', 'voice', 'document', 'sticker'])) {
            $messageType = ($type === 'voice') ? 'audio' : (($type === 'sticker') ? 'image' : $type);
            $mediaData = $msgData[$type] ?? [];
            $body = $mediaData['caption'] ?? $mediaData['filename'] ?? '';
            $mediaMimeType = $mediaData['mime_type'] ?? null;
            $mediaId = $mediaData['id'] ?? null;

            $phoneNumberId = $metadata['phone_number_id'] ?? null;
            if (!$waba && $phoneNumberId) {
                $waba = WhatsappAccount::where('phone_number_id', $phoneNumberId)->first();
            }

            if ($mediaId && $waba && !empty($waba->meta_access_token) && !str_starts_with($waba->meta_access_token, 'mock_')) {
                try {
                    $mediaInfoResponse = \Illuminate\Support\Facades\Http::withToken($waba->meta_access_token)
                        ->timeout(10)
                        ->get("https://graph.facebook.com/v19.0/{$mediaId}");

                    if ($mediaInfoResponse->successful()) {
                        $downloadUrl = $mediaInfoResponse->json('url');
                        $fetchedMime = $mediaInfoResponse->json('mime_type');
                        if ($fetchedMime) {
                            $mediaMimeType = $fetchedMime;
                        }

                        if ($downloadUrl) {
                            $fileResponse = \Illuminate\Support\Facades\Http::withToken($waba->meta_access_token)
                                ->withHeaders(['User-Agent' => 'curl/7.68.0'])
                                ->timeout(30)
                                ->get($downloadUrl);

                            if ($fileResponse->successful()) {
                                $ext = $this->getExtensionFromMime($mediaMimeType, $messageType);
                                $filename = 'incoming_' . time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $ext;
                                $dirPath = public_path('uploads/incoming_media');
                                if (!file_exists($dirPath)) {
                                    mkdir($dirPath, 0755, true);
                                }
                                file_put_contents($dirPath . '/' . $filename, $fileResponse->body());
                                $mediaPath = 'uploads/incoming_media/' . $filename;
                            }
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Error downloading incoming Meta media attachment: ' . $e->getMessage());
                }
            }
        } elseif ($type === 'location') {
            $messageType = 'text';
            $loc = $msgData['location'] ?? [];
            $name = $loc['name'] ?? 'Location';
            $addr = $loc['address'] ?? (($loc['latitude'] ?? '') . ', ' . ($loc['longitude'] ?? ''));
            $body = "📍 {$name}: {$addr}";
        } else {
            $messageType = 'text';
            $body = "[Media Attachment: {$type}]";
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
                'message_type' => $messageType,
                'body' => $body,
                'media_path' => $mediaPath,
                'media_mime_type' => $mediaMimeType,
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
     * Map MIME type or media category to extension.
     */
    private function getExtensionFromMime(?string $mime, string $type): string
    {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/3gpp' => '3gp',
            'audio/aac' => 'aac',
            'audio/mp4' => 'm4a',
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'audio/opus' => 'ogg',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt',
        ];

        if ($mime) {
            $cleanMime = strtolower(explode(';', $mime)[0]);
            if (isset($mimeMap[$cleanMime])) {
                return $mimeMap[$cleanMime];
            }
        }

        switch ($type) {
            case 'image': return 'jpg';
            case 'video': return 'mp4';
            case 'audio': return 'mp3';
            case 'document': return 'pdf';
            default: return 'bin';
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
