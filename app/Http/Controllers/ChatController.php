<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Template;
use App\Models\WhatsappAccount;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Display the Live Chat interface.
     */
    public function index(): View
    {
        $userId = Auth::id();
        
        // Fetch all active, approved templates to populate template sender modal
        $templates = Template::where('user_id', $userId)
            ->where('status', 'APPROVED')
            ->orderBy('name', 'asc')
            ->get();

        // Fetch WABAs for dropdown selector
        $wabas = WhatsappAccount::where('user_id', $userId)
            ->where('status', true)
            ->get();

        // Fetch all contacts to populate start new chat modal dropdown
        $contacts = \App\Models\Contact::where('user_id', $userId)
            ->orderBy('name', 'asc')
            ->get();

        return view('user.chat.index', compact('templates', 'wabas', 'contacts'));
    }

    /**
     * Retrieve a list of conversations for the authenticated user.
     */
    public function conversations(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $query = Conversation::where('user_id', $userId)
            ->with(['contact', 'whatsappAccount']);

        if ($request->filled('waba_id')) {
            $query->where('whatsapp_account_id', $request->query('waba_id'));
        }

        $conversations = $query->orderBy('last_message_at', 'desc')->get();

        // Eager load the last message for each conversation
        $conversations->each(function ($conv) {
            $conv->last_message = Message::where('conversation_id', $conv->id)
                ->where('created_at', '<=', now())
                ->orderBy('created_at', 'desc')
                ->first();
        });

        return response()->json([
            'status' => true,
            'conversations' => $conversations
        ]);
    }

    /**
     * Retrieve all messages in a specific conversation thread.
     */
    public function messages(int $id): JsonResponse
    {
        $userId = Auth::id();
        $conversation = Conversation::where('user_id', $userId)->findOrFail($id);

        // Fetch messages that are ready (created_at <= now)
        $messages = Message::where('conversation_id', $conversation->id)
            ->where('created_at', '<=', now())
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark conversation as read (reset unread count)
        if ($conversation->unread_count > 0) {
            $conversation->update(['unread_count' => 0]);
        }

        // Calculate 24-hour window from the last incoming message
        $lastIncoming = Message::where('conversation_id', $conversation->id)
            ->where('type', 'incoming')
            ->where('created_at', '<=', now())
            ->latest()
            ->first();

        $windowActive = false;
        $windowExpiresAt = null;
        $windowRemainingSeconds = 0;

        if ($lastIncoming) {
            $expiresAt = $lastIncoming->created_at->addHours(24);
            $windowExpiresAt = $expiresAt->toIso8601String();
            $windowRemainingSeconds = max(0, now()->diffInSeconds($expiresAt, false));
            $windowActive = $expiresAt->isFuture();
        }

        return response()->json([
            'status' => true,
            'conversation' => $conversation->load(['contact', 'whatsappAccount']),
            'messages' => $messages,
            'window' => [
                'active' => $windowActive,
                'expires_at' => $windowExpiresAt,
                'remaining_seconds' => $windowRemainingSeconds
            ]
        ]);
    }

    /**
     * Send a text or template message via WhatsApp API (or simulated fallback).
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id,user_id,' . $userId,
            'message_type' => 'required|string|in:text,template',
            'body' => 'required_if:message_type,text|nullable|string',
            'template_id' => 'required_if:message_type,template|nullable|exists:templates,id,user_id,' . $userId,
            'template_variables' => 'nullable|array'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();
        $conversation = Conversation::where('user_id', $userId)->findOrFail($validated['conversation_id']);
        $waba = $conversation->whatsappAccount;
        $contact = $conversation->contact;

        $token = $waba->meta_access_token;
        $isMock = str_starts_with($token, 'mock_');

        $messageType = $validated['message_type'];

        // Determine message body content to save and send
        $bodyText = '';
        $metaTemplateId = null;
        $payload = [];

        if ($messageType === 'template') {
            $template = Template::where('user_id', $userId)->findOrFail($validated['template_id']);
            $metaTemplateId = $template->meta_template_id;
            
            // Format dynamic parameters for Meta API
            $componentsPayload = [];
            $bodyVars = $validated['template_variables'] ?? [];
            
            // Reconstruct text body with bound variables for our local DB record
            $rawBodyText = '';
            foreach ($template->components as $comp) {
                if ($comp['type'] === 'BODY') {
                    $rawBodyText = $comp['text'];
                }
            }

            $parameters = [];
            foreach ($bodyVars as $index => $val) {
                $parameters[] = [
                    'type' => 'text',
                    'text' => $val
                ];
                // Replace variable placeholder locally
                $rawBodyText = str_replace('{{' . ($index + 1) . '}}', $val, $rawBodyText);
            }

            if (!empty($parameters)) {
                $componentsPayload[] = [
                    'type' => 'body',
                    'parameters' => $parameters
                ];
            }

            $bodyText = $rawBodyText;

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
        } else {
            $bodyText = $validated['body'];
            
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $contact->mobile_number,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $bodyText
                ]
            ];
        }

        if ($isMock) {
            // Simulated outgoing message
            $metaMessageId = 'wamid.mock_out_' . Str::random(32);

            $msg = Message::create([
                'user_id' => $userId,
                'conversation_id' => $conversation->id,
                'whatsapp_account_id' => $waba->id,
                'meta_message_id' => $metaMessageId,
                'type' => 'outgoing',
                'message_type' => $messageType,
                'body' => $bodyText,
                'meta_template_id' => $metaTemplateId,
                'status' => 'sent'
            ]);

            // Update conversation details
            $conversation->update([
                'last_message_at' => now()
            ]);

            // Simulate incoming response 1 second in the future
            // Emulate customer typing the same message to test flows natively
            $simulatedBody = $bodyText;

            Message::create([
                'user_id' => $userId,
                'conversation_id' => $conversation->id,
                'whatsapp_account_id' => $waba->id,
                'meta_message_id' => 'wamid.mock_in_' . Str::random(32),
                'type' => 'incoming',
                'message_type' => 'text',
                'body' => $simulatedBody,
                'status' => 'read',
                'created_at' => now()->addSecond(),
                'updated_at' => now()->addSecond()
            ]);

            // Mark unread increment scheduled in the database
            $conversation->increment('unread_count');
            $conversation->update([
                'last_message_at' => now()->addSecond()
            ]);

            // Execute chatbot Flow Executor engine on the incoming simulated customer message!
            // Run this 2 seconds later so it appears sequentially after the customer reply
            dispatch(function () use ($conversation, $simulatedBody) {
                $executor = new \App\Services\FlowExecutorService();
                $executor->handleIncomingMessage($conversation, $simulatedBody);
            })->delay(now()->addSeconds(2));

            return response()->json([
                'status' => true,
                'message' => 'Simulated message sent successfully!',
                'record' => $msg
            ]);
        }

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->post("https://graph.facebook.com/v19.0/{$waba->phone_number_id}/messages", $payload);

            if ($response->successful()) {
                $resData = $response->json();
                $metaMessageId = $resData['messages'][0]['id'] ?? 'unknown_meta_id';

                $msg = Message::create([
                    'user_id' => $userId,
                    'conversation_id' => $conversation->id,
                    'whatsapp_account_id' => $waba->id,
                    'meta_message_id' => $metaMessageId,
                    'type' => 'outgoing',
                    'message_type' => $messageType,
                    'body' => $bodyText,
                    'meta_template_id' => $metaTemplateId,
                    'status' => 'sent'
                ]);

                $conversation->update([
                    'last_message_at' => now()
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Message sent successfully via Meta Cloud API!',
                    'record' => $msg
                ]);
            } else {
                $err = $response->json('error.message') ?? 'Unknown error sending via Meta.';
                return response()->json([
                    'status' => false,
                    'message' => 'Meta send failed: ' . $err
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Chat message send failure: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Connection to Meta API failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start a conversation with a contact from the contacts page.
     */
    public function startChatWithContact(int $contactId)
    {
        $userId = Auth::id();
        $contact = \App\Models\Contact::where('user_id', $userId)->findOrFail($contactId);

        // Find existing conversation
        $conversation = Conversation::where('user_id', $userId)
            ->where('contact_id', $contactId)
            ->first();

        if (!$conversation) {
            // Find the first active WABA account
            $waba = WhatsappAccount::where('user_id', $userId)
                ->where('status', true)
                ->first();

            if (!$waba) {
                return redirect()->route('contacts.index')
                    ->with('error', 'Please configure and connect at least one active WhatsApp Business Account (WABA) first.');
            }

            // Create a new conversation
            $conversation = Conversation::create([
                'user_id' => $userId,
                'whatsapp_account_id' => $waba->id,
                'contact_id' => $contactId,
                'last_message_at' => now(),
                'unread_count' => 0
            ]);
        }

        return redirect()->route('chat.index', ['conversation_id' => $conversation->id]);
    }

    /**
     * Start a chat session via AJAX (modal submit).
     */
    public function startChat(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id,user_id,' . $userId . ',status,1',
            'type' => 'required|string|in:existing,new',
            'contact_id' => 'required_if:type,existing|nullable|exists:contacts,id,user_id,' . $userId,
            'mobile_number' => 'required_if:type,new|nullable|string',
            'name' => 'nullable|string'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();
        $wabaId = $validated['whatsapp_account_id'];

        if ($validated['type'] === 'existing') {
            $contactId = $validated['contact_id'];
        } else {
            // Normalizing/cleaning the number format
            $mobile = preg_replace('/[^0-9+]/', '', $validated['mobile_number']);
            if (!str_starts_with($mobile, '+')) {
                $mobile = '+' . $mobile;
            }

            // Check if contact already exists
            $contact = \App\Models\Contact::where('user_id', $userId)
                ->where('mobile_number', $mobile)
                ->first();

            if (!$contact) {
                $contact = \App\Models\Contact::create([
                    'user_id' => $userId,
                    'name' => $validated['name'] ?: $mobile,
                    'mobile_number' => $mobile
                ]);
            }
            $contactId = $contact->id;
        }

        // Find or create conversation
        $conversation = Conversation::firstOrCreate([
            'user_id' => $userId,
            'whatsapp_account_id' => $wabaId,
            'contact_id' => $contactId
        ], [
            'last_message_at' => now(),
            'unread_count' => 0
        ]);

        return response()->json([
            'status' => true,
            'conversation_id' => $conversation->id,
            'message' => 'Chat session started successfully.'
        ]);
    }
}
