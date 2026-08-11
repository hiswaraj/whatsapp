<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IncomingMediaWebhookTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private WhatsappAccount $waba;

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
            'whatsapp_business_account_id' => '123456789',
            'phone_number_id' => '987654321',
            'meta_app_id' => 'app_12345',
            'display_name' => 'Test WABA',
            'mobile_number' => '+1234567890',
            'meta_access_token' => 'mock_access_token',
            'verify_token' => 'test_verify_token',
            'status' => 'connected',
        ]);
    }

    public function test_incoming_image_webhook_saves_media_type_and_caption(): void
    {
        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => '987654321',
                                ],
                                'contacts' => [
                                    [
                                        'wa_id' => '919999999999',
                                        'profile' => [
                                            'name' => 'John Doe',
                                        ],
                                    ],
                                ],
                                'messages' => [
                                    [
                                        'from' => '919999999999',
                                        'id' => 'wamid.HBgLMTEx',
                                        'timestamp' => '1723400000',
                                        'type' => 'image',
                                        'image' => [
                                            'id' => 'media_id_123',
                                            'mime_type' => 'image/jpeg',
                                            'caption' => 'Sample Image Caption',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('webhook.whatsapp.handle', 'test_verify_token'), $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('messages', [
            'meta_message_id' => 'wamid.HBgLMTEx',
            'type' => 'incoming',
            'message_type' => 'image',
            'body' => 'Sample Image Caption',
            'media_mime_type' => 'image/jpeg',
        ]);
    }

    public function test_incoming_document_webhook_saves_document_type(): void
    {
        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => '987654321',
                                ],
                                'messages' => [
                                    [
                                        'from' => '919999999999',
                                        'id' => 'wamid.HBgLMjIy',
                                        'timestamp' => '1723400000',
                                        'type' => 'document',
                                        'document' => [
                                            'id' => 'doc_id_456',
                                            'mime_type' => 'application/pdf',
                                            'filename' => 'invoice.pdf',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('webhook.whatsapp.handle', 'test_verify_token'), $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('messages', [
            'meta_message_id' => 'wamid.HBgLMjIy',
            'type' => 'incoming',
            'message_type' => 'document',
            'body' => 'invoice.pdf',
            'media_mime_type' => 'application/pdf',
        ]);
    }
}
