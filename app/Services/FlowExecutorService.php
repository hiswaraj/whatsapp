<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Flow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FlowExecutorService
{
    /**
     * Process an incoming message text from a contact.
     *
     * @param Conversation $conversation
     * @param string $incomingText
     */
    public function handleIncomingMessage(Conversation $conversation, string $incomingText)
    {
        $userId = $conversation->user_id;
        $incomingText = trim($incomingText);

        if (empty($incomingText)) {
            return;
        }

        // Check if there is an active flow session
        if ($conversation->active_flow_id && $conversation->current_flow_node_id) {
            $flow = Flow::where('user_id', $userId)->where('is_active', true)->find($conversation->active_flow_id);
            if ($flow) {
                // Execute next node step with isIncoming = true since it is triggered by customer text response
                $this->executeFlowStep($conversation, $flow, $incomingText, true);
                return;
            } else {
                // Clear state if flow was deleted or deactivated
                $this->endFlowSession($conversation);
            }
        }

        // If no active flow session, match trigger keywords
        $flows = Flow::where('user_id', $userId)->where('is_active', true)->get();
        foreach ($flows as $flow) {
            $keywords = $flow->trigger_keywords ?? [];
            foreach ($keywords as $kw) {
                if (strcasecmp(trim($kw), $incomingText) === 0) {
                    // Match found! Initialize flow session
                    $compiled = $flow->compiled_data;
                    $startNodeId = $compiled['start_node_id'] ?? null;
                    if ($startNodeId) {
                        $conversation->update([
                            'active_flow_id' => $flow->id,
                            'current_flow_node_id' => $startNodeId
                        ]);
                        // Execute trigger node transition
                        $this->executeFlowStep($conversation, $flow, $incomingText, false);
                        return;
                    }
                }
            }
        }
    }

    /**
     * Execute flow steps starting from the current pointer.
     *
     * @param Conversation $conversation
     * @param Flow $flow
     * @param string $incomingText
     * @param bool $isIncoming
     */
    protected function executeFlowStep(Conversation $conversation, Flow $flow, string $incomingText, bool $isIncoming = false)
    {
        $compiled = $flow->compiled_data;
        $nodes = $compiled['nodes'] ?? [];
        $currentNodeId = $conversation->current_flow_node_id;

        if (!isset($nodes[$currentNodeId])) {
            $this->endFlowSession($conversation);
            return;
        }

        $node = $nodes[$currentNodeId];
        $nodeType = $node['type'] ?? '';

        if ($nodeType === 'trigger') {
            // Trigger node is just a starting placeholder. Skip to its output.
            $nextNodeId = $node['next_node_id'] ?? null;
            if ($nextNodeId) {
                $conversation->update(['current_flow_node_id' => $nextNodeId]);
                $this->executeFlowStep($conversation, $flow, $incomingText, $isIncoming);
            } else {
                $this->endFlowSession($conversation);
            }
            return;
        }

        if ($nodeType === 'send_message') {
            // Send the text message
            $messageText = $node['message'] ?? '';
            $this->sendFlowMessage($conversation, $messageText);

            // Move to next node immediately
            $nextNodeId = $node['next_node_id'] ?? null;
            if ($nextNodeId) {
                $conversation->update(['current_flow_node_id' => $nextNodeId]);
                $this->executeFlowStep($conversation, $flow, $incomingText, $isIncoming);
            } else {
                $this->endFlowSession($conversation);
            }
            return;
        }

        if ($nodeType === 'menu') {
            $options = $node['options'] ?? [];

            if ($isIncoming) {
                // The user is replying to the menu we already sent. Evaluate option match!
                $matchedNodeId = null;
                foreach ($options as $optIdx => $opt) {
                    $optText = trim($opt['text'] ?? '');
                    if (
                        strcasecmp($incomingText, (string)$optIdx) === 0 ||
                        strcasecmp($incomingText, $optText) === 0
                    ) {
                        $matchedNodeId = $opt['next_node_id'] ?? null;
                        break;
                    }
                }

                if ($matchedNodeId) {
                    // Update pointer and execute next node (as a non-incoming flow continuation)
                    $conversation->update(['current_flow_node_id' => $matchedNodeId]);
                    $this->executeFlowStep($conversation, $flow, $incomingText, false);
                } else {
                    // Resend options on invalid choice
                    $messageText = $node['message'] ?? 'Please choose one of the options:';
                    $formattedMessage = "⚠️ Invalid choice.\n" . $messageText . "\n" . $this->formatMenuOptions($options);
                    $this->sendFlowMessage($conversation, $formattedMessage);
                }
            } else {
                // We just entered the menu node from a prior action. Send options and halt!
                $messageText = $node['message'] ?? 'Please choose one of the options:';
                $formattedMessage = $messageText . "\n" . $this->formatMenuOptions($options);
                $this->sendFlowMessage($conversation, $formattedMessage);
                
                // Halt execution at this node (pointer remains at current node, waiting for response)
            }
            return;
        }

        // End flow if node type is unknown
        $this->endFlowSession($conversation);
    }

    /**
     * Format list of options to readable text menu items.
     *
     * @param array $options
     * @return string
     */
    protected function formatMenuOptions(array $options): string
    {
        $lines = [];
        foreach ($options as $idx => $opt) {
            $lines[] = "🔹 *{$idx}*. " . ($opt['text'] ?? '');
        }
        return implode("\n", $lines);
    }

    /**
     * Terminate active flow tracking on conversation.
     *
     * @param Conversation $conversation
     */
    protected function endFlowSession(Conversation $conversation)
    {
        $conversation->update([
            'active_flow_id' => null,
            'current_flow_node_id' => null
        ]);
    }

    /**
     * Transmit reply message (triggers real Meta Cloud API or mock reply based on verify token).
     *
     * @param Conversation $conversation
     * @param string $text
     */
    protected function sendFlowMessage(Conversation $conversation, string $text)
    {
        if (empty($text)) return;

        $waba = $conversation->whatsappAccount;
        $contact = $conversation->contact;
        $userId = $conversation->user_id;

        $token = $waba->meta_access_token;
        $isMock = str_starts_with($token, 'mock_');

        // Clean values before sending
        $text = str_replace('{{name}}', $contact->name ?? 'Recipient', $text);
        $text = str_replace('{{mobile}}', $contact->mobile_number ?? '', $text);

        if ($isMock) {
            // Simulated outgoing chat message from WABA
            Message::create([
                'user_id' => $userId,
                'conversation_id' => $conversation->id,
                'whatsapp_account_id' => $waba->id,
                'meta_message_id' => 'wamid.mock_flow_' . Str::random(32),
                'type' => 'outgoing',
                'message_type' => 'text',
                'body' => $text,
                'status' => 'read'
            ]);
            
            $conversation->update(['last_message_at' => now()]);
        } else {
            // Real Meta Cloud API send
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $contact->mobile_number,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $text
                ]
            ];

            try {
                $response = Http::withToken($token)
                    ->timeout(10)
                    ->post("https://graph.facebook.com/v19.0/{$waba->phone_number_id}/messages", $payload);

                if ($response->successful()) {
                    $resData = $response->json();
                    $metaMessageId = $resData['messages'][0]['id'] ?? 'wamid.flow_' . Str::random(16);

                    Message::create([
                        'user_id' => $userId,
                        'conversation_id' => $conversation->id,
                        'whatsapp_account_id' => $waba->id,
                        'meta_message_id' => $metaMessageId,
                        'type' => 'outgoing',
                        'message_type' => 'text',
                        'body' => $text,
                        'status' => 'sent'
                    ]);

                    $conversation->update(['last_message_at' => now()]);
                } else {
                    $err = $response->json('error.message') ?? 'Flow API send error';
                    Log::error("Flow builder auto-reply failed: " . $err);
                }
            } catch (\Exception $e) {
                Log::error("Flow builder HTTP connection failed: " . $e->getMessage());
            }
        }
    }
}
