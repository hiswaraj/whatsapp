<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Contact;
use App\Models\WhatsappAccount;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatExpiredWindowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private WhatsappAccount $waba;
    private Contact $contact;
    private Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->waba = WhatsappAccount::create([
            'user_id' => $this->user->id,
            'display_name' => 'Test Account',
            'meta_access_token' => 'mock_123456',
            'phone_number_id' => '123456789',
            'whatsapp_business_account_id' => '987654321',
            'meta_app_id' => '12345',
            'verify_token' => 'verify_token_xyz',
            'status' => true,
        ]);

        $this->contact = Contact::create([
            'user_id' => $this->user->id,
            'name' => 'Swaraj',
            'mobile_number' => '+919999999999',
        ]);

        $this->conversation = Conversation::create([
            'user_id' => $this->user->id,
            'whatsapp_account_id' => $this->waba->id,
            'contact_id' => $this->contact->id,
            'last_message_at' => now(),
            'unread_count' => 0,
        ]);
    }

    public function test_cannot_send_normal_message_when_no_incoming_message_exists(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('chat.messages.send'), [
                'conversation_id' => $this->conversation->id,
                'message_type' => 'text',
                'body' => 'Hello Swaraj',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status' => false,
            'message' => 'The 24-hour chat window has expired. You can only send template messages to this contact.',
        ]);
    }

    public function test_cannot_send_normal_message_when_last_incoming_message_older_than_24_hours(): void
    {
        // Create an incoming message older than 24 hours
        $msg = new Message([
            'user_id' => $this->user->id,
            'conversation_id' => $this->conversation->id,
            'whatsapp_account_id' => $this->waba->id,
            'meta_message_id' => 'mock_incoming_123',
            'type' => 'incoming',
            'message_type' => 'text',
            'body' => 'Hi',
        ]);
        $msg->created_at = now()->subHours(25);
        $msg->save();

        $response = $this->actingAs($this->user)
            ->postJson(route('chat.messages.send'), [
                'conversation_id' => $this->conversation->id,
                'message_type' => 'text',
                'body' => 'Hello Swaraj',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status' => false,
            'message' => 'The 24-hour chat window has expired. You can only send template messages to this contact.',
        ]);
    }

    public function test_can_send_normal_message_when_last_incoming_message_within_24_hours(): void
    {
        // Create an incoming message within 24 hours
        $msg = new Message([
            'user_id' => $this->user->id,
            'conversation_id' => $this->conversation->id,
            'whatsapp_account_id' => $this->waba->id,
            'meta_message_id' => 'mock_incoming_123',
            'type' => 'incoming',
            'message_type' => 'text',
            'body' => 'Hi',
        ]);
        $msg->created_at = now()->subHours(23);
        $msg->save();

        $response = $this->actingAs($this->user)
            ->postJson(route('chat.messages.send'), [
                'conversation_id' => $this->conversation->id,
                'message_type' => 'text',
                'body' => 'Hello Swaraj',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);
    }

    public function test_can_send_template_message_even_when_chat_window_is_expired(): void
    {
        $template = Template::create([
            'user_id' => $this->user->id,
            'whatsapp_account_id' => $this->waba->id,
            'meta_template_id' => 'tpl_123',
            'name' => 'welcome_template',
            'language' => 'en',
            'category' => 'UTILITY',
            'status' => 'APPROVED',
            'components' => [
                [
                    'type' => 'BODY',
                    'text' => 'Hello {{1}}, welcome!',
                ]
            ],
        ]);

        // Attempt sending template (no incoming message)
        $response = $this->actingAs($this->user)
            ->postJson(route('chat.messages.send'), [
                'conversation_id' => $this->conversation->id,
                'message_type' => 'template',
                'template_id' => $template->id,
                'template_variables' => ['Swaraj'],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);
    }

    public function test_can_send_template_message_with_media_header_attachment(): void
    {
        // 1. Create media asset
        $media = \App\Models\MediaLibrary::create([
            'user_id' => $this->user->id,
            'filename' => 'test_image.png',
            'file_path' => 'uploads/media/test_image.png',
            'file_type' => 'image',
            'file_size' => 1024,
        ]);

        // 2. Create template with header media
        $template = Template::create([
            'user_id' => $this->user->id,
            'whatsapp_account_id' => $this->waba->id,
            'meta_template_id' => 'tpl_media_123',
            'name' => 'welcome_media_template',
            'language' => 'en',
            'category' => 'UTILITY',
            'status' => 'APPROVED',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'IMAGE',
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Hello {{1}}, welcome!',
                ]
            ],
        ]);

        // 3. Dispatch template message with media header
        $response = $this->actingAs($this->user)
            ->postJson(route('chat.messages.send'), [
                'conversation_id' => $this->conversation->id,
                'message_type' => 'template',
                'template_id' => $template->id,
                'template_variables' => ['Swaraj'],
                'header_media_id' => $media->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);

        // 4. Assert message was saved with the media_path
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->conversation->id,
            'message_type' => 'template',
            'media_path' => 'uploads/media/test_image.png',
        ]);
    }
}
