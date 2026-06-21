<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

#[Signature('campaigns:process')]
#[Description('Process scheduled and active WhatsApp template broadcast campaigns.')]
class ProcessCampaigns extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        // Step 1: Activate scheduled campaigns whose trigger time has arrived
        $scheduledCampaigns = Campaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();

        foreach ($scheduledCampaigns as $camp) {
            $camp->update(['status' => 'processing']);
            $this->info("Campaign '{$camp->name}' activated from scheduled to processing status.");
        }

        // Step 2: Retrieve all active campaigns currently processing
        $campaigns = Campaign::where('status', 'processing')->get();

        if ($campaigns->isEmpty()) {
            $this->info("No active processing campaigns found.");
            return Command::SUCCESS;
        }

        foreach ($campaigns as $campaign) {
            $this->info("Processing campaign: {$campaign->name} (ID: {$campaign->id})");

            $waba = $campaign->whatsappAccount;
            $template = $campaign->template;
            $group = $campaign->contactGroup;

            if (!$waba || !$template) {
                $campaign->update(['status' => 'failed']);
                $this->error("Campaign '{$campaign->name}' failed due to missing WABA or Template.");
                continue;
            }

            if (!$group) {
                // Quick Broadcast Mode: pending contacts are those with pending messages in the database
                $pendingMessages = Message::where('campaign_id', $campaign->id)
                    ->where('status', 'pending')
                    ->with('conversation.contact')
                    ->limit(20)
                    ->get();

                if ($pendingMessages->isEmpty()) {
                    $campaign->update(['status' => 'completed']);
                    $this->info("Quick Broadcast '{$campaign->name}' completed! All messages sent.");
                    continue;
                }

                $this->info("Found " . $pendingMessages->count() . " pending messages for Quick Broadcast. Processing batch...");

                foreach ($pendingMessages as $msg) {
                    // Refresh campaign status dynamically inside the loop. If paused or stopped, abort immediately
                    $currentStatus = Campaign::where('id', $campaign->id)->value('status');
                    if ($currentStatus !== 'processing') {
                        $this->warn("Campaign status changed to '{$currentStatus}'. Halting send loop.");
                        break 2; // Exit contact loop and campaign loop
                    }

                    $contact = $msg->conversation->contact ?? null;
                    if (!$contact) {
                        $msg->update(['status' => 'failed', 'error_message' => 'Associated contact not found.']);
                        $campaign->increment('failed_count');
                        continue;
                    }

                    $token = $waba->meta_access_token;
                    $isMock = str_starts_with($token, 'mock_');

                    if ($isMock) {
                        // Simulated Outgoing Message
                        $metaMessageId = 'wamid.mock_camp_' . Str::random(32);

                        $msg->update([
                            'meta_message_id' => $metaMessageId,
                            'status' => 'sent'
                        ]);

                        $campaign->increment('sent_count');
                        $msg->conversation->update(['last_message_at' => now()]);

                        // Dispatch simulated status updates in background
                        dispatch(new \App\Jobs\SimulateWebhookReceipt($msg->id, 'delivered'))->delay(now()->addSeconds(3));
                        dispatch(new \App\Jobs\SimulateWebhookReceipt($msg->id, 'read'))->delay(now()->addSeconds(6));
                    } else {
                        // Meta API Outgoing payload construction
                        $componentsPayload = [];

                        // 1. Compile Header media if applicable
                        $headerFormat = null;
                        if ($template && $template->components) {
                            foreach ($template->components as $comp) {
                                if (($comp['type'] ?? '') === 'HEADER') {
                                    $headerFormat = strtolower($comp['format'] ?? '');
                                }
                            }
                        }

                        if ($headerFormat && in_array($headerFormat, ['image', 'video', 'document']) && !empty($msg->media_path)) {
                            $mediaUrl = $msg->media_path;
                            if (!str_starts_with($mediaUrl, 'http://') && !str_starts_with($mediaUrl, 'https://')) {
                                $mediaUrl = asset($mediaUrl);
                            }

                            $headerParam = [
                                'type' => $headerFormat,
                                $headerFormat => [
                                    'link' => $mediaUrl
                                ]
                            ];

                            if ($headerFormat === 'document') {
                                $headerParam['document']['filename'] = basename($mediaUrl);
                            }

                            $componentsPayload[] = [
                                'type' => 'header',
                                'parameters' => [$headerParam]
                            ];
                        }

                        // 2. Compile Body Parameters
                        $templateParams = $msg->template_params ?? [];
                        if (!empty($templateParams)) {
                            $parameters = [];
                            foreach ($templateParams as $val) {
                                $parameters[] = [
                                    'type' => 'text',
                                    'text' => (string)$val
                                ];
                            }
                            $componentsPayload[] = [
                                'type' => 'body',
                                'parameters' => $parameters
                            ];
                        }

                        $payload = [
                            'messaging_product' => 'whatsapp',
                            'to' => $contact->mobile_number,
                            'type' => 'template',
                            'template' => [
                                'name' => $template->name,
                                'language' => [
                                    'code' => $template->language
                                ],
                                'components' => $componentsPayload
                            ]
                        ];

                        try {
                            $response = Http::withToken($token)
                                ->timeout(10)
                                ->post("https://graph.facebook.com/v19.0/{$waba->phone_number_id}/messages", $payload);

                            if ($response->successful()) {
                                $resData = $response->json();
                                $metaMessageId = $resData['messages'][0]['id'] ?? 'wamid.api_' . Str::random(16);

                                $msg->update([
                                    'meta_message_id' => $metaMessageId,
                                    'status' => 'sent'
                                ]);

                                $campaign->increment('sent_count');
                                $msg->conversation->update(['last_message_at' => now()]);
                            } else {
                                $errorMsg = $response->json()['error']['message'] ?? 'Meta API validation failed';

                                $msg->update([
                                    'status' => 'failed',
                                    'error_message' => $errorMsg
                                ]);

                                $campaign->increment('failed_count');
                            }
                        } catch (\Exception $e) {
                            $msg->update([
                                'status' => 'failed',
                                'error_message' => $e->getMessage()
                            ]);

                            $campaign->increment('failed_count');
                        }
                    }

                    // Short sleep delay to control broadcast rate
                    sleep(1);
                }

                continue; // Move to the next campaign in loop
            }

            // Step 3: Find contacts who haven't received a message from this campaign yet
            // Querying message campaign_id via conversations
            $sentContactIds = Conversation::where('whatsapp_account_id', $campaign->whatsapp_account_id)
                ->whereHas('messages', function ($q) use ($campaign) {
                    $q->where('campaign_id', $campaign->id);
                })
                ->pluck('contact_id')
                ->toArray();

            // Fetch a batch of pending contacts (20 per minute to prevent Meta rate limits and local script timeouts)
            $pendingContacts = $group->contacts()
                ->whereNotIn('contacts.id', $sentContactIds)
                ->limit(20)
                ->get();

            if ($pendingContacts->isEmpty()) {
                $campaign->update(['status' => 'completed']);
                $this->info("Campaign '{$campaign->name}' completed! All messages sent.");
                continue;
            }

            $this->info("Found " . $pendingContacts->count() . " pending contacts. Processing batch...");

            foreach ($pendingContacts as $contact) {
                // Refresh campaign status dynamically inside the loop. If paused or stopped, abort immediately
                $currentStatus = Campaign::where('id', $campaign->id)->value('status');
                if ($currentStatus !== 'processing') {
                    $this->warn("Campaign status changed to '{$currentStatus}'. Halting send loop.");
                    break 2; // Exit contact loop and campaign loop
                }

                // Double check if sent to prevent duplicate sends (race conditions)
                $alreadySent = Conversation::where('contact_id', $contact->id)
                    ->where('whatsapp_account_id', $campaign->whatsapp_account_id)
                    ->whereHas('messages', function ($q) use ($campaign) {
                        $q->where('campaign_id', $campaign->id);
                    })
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                // Resolve conversation
                $conversation = Conversation::firstOrCreate([
                    'user_id' => $campaign->user_id,
                    'whatsapp_account_id' => $campaign->whatsapp_account_id,
                    'contact_id' => $contact->id,
                ], [
                    'last_message_at' => now(),
                    'unread_count' => 0
                ]);

                // Bind template variables
                $templateVariables = $campaign->template_variables ?? [];
                $processedParams = [];
                foreach ($templateVariables as $variable) {
                    $val = $variable;
                    $val = str_replace('{{name}}', $contact->name ?? '', $val);
                    $val = str_replace('{{email}}', $contact->email ?? '', $val);
                    $val = str_replace('{{mobile}}', $contact->mobile_number ?? '', $val);
                    $processedParams[] = $val;
                }

                // Build body text locally to log message content in the database
                $bodyText = '';
                foreach ($template->components as $comp) {
                    if ($comp['type'] === 'BODY') {
                        $bodyText = $comp['text'];
                    }
                }
                foreach ($processedParams as $index => $val) {
                    $bodyText = str_replace('{{' . ($index + 1) . '}}', $val, $bodyText);
                }

                $token = $waba->meta_access_token;
                $isMock = str_starts_with($token, 'mock_');

                if ($isMock) {
                    // Simulated Outgoing Message
                    $metaMessageId = 'wamid.mock_camp_' . Str::random(32);

                    $message = Message::create([
                        'user_id' => $campaign->user_id,
                        'conversation_id' => $conversation->id,
                        'campaign_id' => $campaign->id,
                        'whatsapp_account_id' => $waba->id,
                        'meta_message_id' => $metaMessageId,
                        'type' => 'outgoing',
                        'message_type' => 'template',
                        'body' => $bodyText,
                        'meta_template_id' => $template->meta_template_id,
                        'status' => 'sent'
                    ]);

                    $campaign->increment('sent_count');
                    $conversation->update(['last_message_at' => now()]);

                    // Dispatch simulated status updates in background
                    dispatch(new \App\Jobs\SimulateWebhookReceipt($message->id, 'delivered'))->delay(now()->addSeconds(3));
                    dispatch(new \App\Jobs\SimulateWebhookReceipt($message->id, 'read'))->delay(now()->addSeconds(6));
                } else {
                    // Meta API Outgoing payload construction
                    $componentsPayload = [];
                    if (!empty($processedParams)) {
                        $parameters = [];
                        foreach ($processedParams as $val) {
                            $parameters[] = [
                                'type' => 'text',
                                'text' => $val
                            ];
                        }
                        $componentsPayload[] = [
                            'type' => 'body',
                            'parameters' => $parameters
                        ];
                    }

                    $payload = [
                        'messaging_product' => 'whatsapp',
                        'to' => $contact->mobile_number,
                        'type' => 'template',
                        'template' => [
                            'name' => $template->name,
                            'language' => [
                                'code' => $template->language
                            ],
                            'components' => $componentsPayload
                        ]
                    ];

                    try {
                        $response = Http::withToken($token)
                            ->timeout(10)
                            ->post("https://graph.facebook.com/v19.0/{$waba->phone_number_id}/messages", $payload);

                        if ($response->successful()) {
                            $resData = $response->json();
                            $metaMessageId = $resData['messages'][0]['id'] ?? 'wamid.api_' . Str::random(16);

                            Message::create([
                                'user_id' => $campaign->user_id,
                                'conversation_id' => $conversation->id,
                                'campaign_id' => $campaign->id,
                                'whatsapp_account_id' => $waba->id,
                                'meta_message_id' => $metaMessageId,
                                'type' => 'outgoing',
                                'message_type' => 'template',
                                'body' => $bodyText,
                                'meta_template_id' => $template->meta_template_id,
                                'status' => 'sent'
                            ]);

                            $campaign->increment('sent_count');
                            $conversation->update(['last_message_at' => now()]);
                        } else {
                            $errorMsg = $response->json()['error']['message'] ?? 'Meta API validation failed';

                            Message::create([
                                'user_id' => $campaign->user_id,
                                'conversation_id' => $conversation->id,
                                'campaign_id' => $campaign->id,
                                'whatsapp_account_id' => $waba->id,
                                'type' => 'outgoing',
                                'message_type' => 'template',
                                'body' => $bodyText,
                                'meta_template_id' => $template->meta_template_id,
                                'status' => 'failed',
                                'error_message' => $errorMsg
                            ]);

                            $campaign->increment('failed_count');
                        }
                    } catch (\Exception $e) {
                        Message::create([
                            'user_id' => $campaign->user_id,
                            'conversation_id' => $conversation->id,
                            'campaign_id' => $campaign->id,
                            'whatsapp_account_id' => $waba->id,
                            'type' => 'outgoing',
                            'message_type' => 'template',
                            'body' => $bodyText,
                            'meta_template_id' => $template->meta_template_id,
                            'status' => 'failed',
                            'error_message' => $e->getMessage()
                        ]);

                        $campaign->increment('failed_count');
                    }
                }

                // Short sleep delay to control broadcast rate
                sleep(1);
            }
        }

        return Command::SUCCESS;
    }
}
