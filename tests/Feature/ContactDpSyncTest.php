<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContactDpSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Contact $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->contact = Contact::create([
            'user_id' => $this->user->id,
            'name' => 'Swaraj Mandal',
            'mobile_number' => '+919999999999',
        ]);
    }

    public function test_contact_dp_sync_success(): void
    {
        // Mock the HTTP call to return a mock image
        Http::fake([
            'https://images.unsplash.com/*' => Http::response('fake-image-binary-data', 200)
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('contacts.sync-dp', $this->contact->id));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Contact display picture synced successfully!',
        ]);

        // Refetch contact and assert avatar_url is set
        $this->contact->refresh();
        $this->assertNotNull($this->contact->avatar_url);
        $this->assertStringContainsString('uploads/contact_avatars/contact_avatar_sync_', $this->contact->avatar_url);

        // Check if the physical file exists
        $filePath = public_path($this->contact->avatar_url);
        $this->assertTrue(file_exists($filePath));

        // Clean up the created test image
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }
}
