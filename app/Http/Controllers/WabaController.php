<?php

namespace App\Http\Controllers;

use App\Models\WhatsappAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WabaController extends Controller
{
    /**
     * Display a listing of WABAs.
     */
    public function index(): View
    {
        $userId = Auth::id();
        $wabas = WhatsappAccount::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.wabas.index', compact('wabas'));
    }

    /**
     * Store a newly created WABA in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'display_name' => 'required|string|max:255',
            'meta_access_token' => 'required|string',
            'phone_number_id' => 'required|string|max:255',
            'whatsapp_business_account_id' => 'required|string|max:255',
            'meta_app_id' => 'required|string|max:255',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        // Check for duplicates (phone_number_id) scoped to this user
        $exists = WhatsappAccount::where('user_id', $userId)
            ->where('phone_number_id', $validated['phone_number_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'A WABA account with this Phone Number ID is already registered.'
            ], 422);
        }

        $waba = WhatsappAccount::create([
            'user_id' => $userId,
            'display_name' => $validated['display_name'],
            'meta_access_token' => $validated['meta_access_token'],
            'phone_number_id' => $validated['phone_number_id'],
            'whatsapp_business_account_id' => $validated['whatsapp_business_account_id'],
            'meta_app_id' => $validated['meta_app_id'],
            'verify_token' => $this->generateUniqueVerifyToken(),
            'status' => true
        ]);

        return response()->json([
            'status' => true,
            'message' => 'WhatsApp Business Account added successfully!',
            'waba' => $waba
        ]);
    }

    /**
     * Update the specified WABA in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $userId = Auth::id();
        $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($id);

        $validation = Validator::make($request->all(), [
            'display_name' => 'required|string|max:255',
            'meta_access_token' => 'required|string',
            'phone_number_id' => 'required|string|max:255',
            'whatsapp_business_account_id' => 'required|string|max:255',
            'meta_app_id' => 'required|string|max:255',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        // Check for duplicates (phone_number_id) excluding this record
        $exists = WhatsappAccount::where('user_id', $userId)
            ->where('phone_number_id', $validated['phone_number_id'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Another WABA account with this Phone Number ID is already registered.'
            ], 422);
        }

        $waba->update([
            'display_name' => $validated['display_name'],
            'meta_access_token' => $validated['meta_access_token'],
            'phone_number_id' => $validated['phone_number_id'],
            'whatsapp_business_account_id' => $validated['whatsapp_business_account_id'],
            'meta_app_id' => $validated['meta_app_id'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'WhatsApp Business Account updated successfully!',
            'waba' => $waba
        ]);
    }

    /**
     * Remove the specified WABA from storage.
     */
    public function destroy($id): JsonResponse
    {
        $userId = Auth::id();
        $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($id);
        $waba->delete();

        return response()->json([
            'status' => true,
            'message' => 'WhatsApp Business Account deleted successfully!'
        ]);
    }

    /**
     * Toggle the active status of a WABA.
     */
    public function toggleStatus($id): JsonResponse
    {
        $userId = Auth::id();
        $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($id);
        
        $waba->status = !$waba->status;
        $waba->save();

        return response()->json([
            'status' => true,
            'message' => 'Account status updated successfully.',
            'new_status' => $waba->status
        ]);
    }

    /**
     * Verify credentials against Meta Graph API (Simulated/Actual fallback).
     */
    public function verifyConnection(Request $request, $id): JsonResponse
    {
        $userId = Auth::id();
        $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($id);

        // Simulation delay to feel organic
        usleep(800000); // 0.8 seconds

        $token = $waba->meta_access_token;
        
        // Only trigger mock/simulated success if the access token explicitly starts with 'mock_'
        $isMock = str_starts_with($token, 'mock_');

        if ($isMock) {
            return response()->json([
                'status' => true,
                'message' => 'Simulated connection verification successful!',
                'meta_details' => [
                    'account_status' => 'APPROVED',
                    'quality_rating' => 'GREEN (High)',
                    'verified_name' => $waba->display_name,
                    'phone_number' => '+1 555-019-2831',
                    'timezone' => 'UTC',
                    'currency' => 'USD',
                ]
            ]);
        }

        try {
            $response = Http::withToken($token)
                ->timeout(5)
                ->get("https://graph.facebook.com/v19.0/{$waba->whatsapp_business_account_id}");

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'status' => true,
                    'message' => 'Meta Graph API connection verified successfully!',
                    'meta_details' => [
                        'account_status' => $data['status'] ?? 'APPROVED',
                        'quality_rating' => 'Verified',
                        'verified_name' => $data['name'] ?? $waba->display_name,
                        'phone_number' => 'Connected',
                        'raw_response' => $data
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Verification failed: ' . ($response->json('error.message') ?? 'Invalid token or account ID.'),
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Verification failed: Connection to Meta API failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Send a test message using Meta Cloud API structure (Simulated/Actual fallback).
     */
    public function testMessage(Request $request, $id): JsonResponse
    {
        $userId = Auth::id();
        $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($id);

        $validation = Validator::make($request->all(), [
            'to_number' => 'required|string|max:30',
            'message_type' => 'required|string|in:text,template',
            'template_name' => 'required_if:message_type,template|nullable|string|max:255',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();
        $toNumber = $validated['to_number'];
        $messageType = $validated['message_type'];

        // Build Payload
        if ($messageType === 'template') {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $toNumber,
                'type' => 'template',
                'template' => [
                    'name' => $validated['template_name'] ?? 'hello_world',
                    'language' => [
                        'code' => 'en_US'
                    ]
                ]
            ];
        } else {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $toNumber,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => 'This is a WhatsApp SaaS test verification message from ' . $waba->display_name
                ]
            ];
        }

        // Simulation delay
        usleep(900000); // 0.9 seconds

        $token = $waba->meta_access_token;
        
        // Only trigger mock/simulated send if the access token explicitly starts with 'mock_'
        $isMock = str_starts_with($token, 'mock_');

        if ($isMock) {
            return response()->json([
                'status' => true,
                'message' => 'Simulated message sent successfully!',
                'payload_sent' => $payload,
                'meta_response' => [
                    'messaging_product' => 'whatsapp',
                    'contacts' => [
                        ['input' => $toNumber, 'wa_id' => str_replace('+', '', $toNumber)]
                    ],
                    'messages' => [
                        ['id' => 'wamid.' . Str::random(32)]
                    ]
                ]
            ]);
        }

        try {
            $response = Http::withToken($token)
                ->timeout(5)
                ->post("https://graph.facebook.com/v19.0/{$waba->phone_number_id}/messages", $payload);

            if ($response->successful()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Test message sent successfully via Meta Cloud API!',
                    'payload_sent' => $payload,
                    'meta_response' => $response->json()
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Meta send failed: ' . ($response->json('error.message') ?? 'Unknown API error.'),
                    'payload_sent' => $payload
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Send failed: Connection to Meta API failed: ' . $e->getMessage(),
                'payload_sent' => $payload
            ], 400);
        }
    }

    /**
     * Regenerate the webhook verify token for a specific WABA.
     */
    public function regenerateVerifyToken($id): JsonResponse
    {
        $userId = Auth::id();
        $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($id);

        $newToken = $this->generateUniqueVerifyToken();
        $waba->verify_token = $newToken;
        $waba->save();

        return response()->json([
            'status' => true,
            'message' => 'Verify Token regenerated successfully!',
            'verify_token' => $newToken,
            'webhook_url' => url('/webhook/whatsapp/' . $newToken)
        ]);
    }

    /**
     * Sync/Fetch DP from Meta.
     */
    public function syncDp($id): JsonResponse
    {
        $userId = Auth::id();
        $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($id);

        $token = $waba->meta_access_token;
        $isMock = str_starts_with($token, 'mock_');

        $destinationPath = public_path('uploads/waba_dps');
        if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
            \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true);
        }

        if ($isMock) {
            // Simulated/Mock DP URL
            $mockUrls = [
                "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80",
                "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80",
                "https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80"
            ];
            // Pick one mock URL based on ID
            $selectedUrl = $mockUrls[$waba->id % count($mockUrls)];
            
            try {
                $imageContent = file_get_contents($selectedUrl);
                if ($imageContent) {
                    $filename = "waba_dp_{$waba->id}.jpg";
                    \Illuminate\Support\Facades\File::put($destinationPath . '/' . $filename, $imageContent);
                    $waba->profile_picture_url = 'uploads/waba_dps/' . $filename;
                    $waba->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Mock profile picture fetched successfully!',
                        'profile_picture_url' => asset($waba->profile_picture_url)
                    ]);
                }
            } catch (\Exception $e) {
                // Fallback if network request fails
            }

            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve mock profile picture.'
            ], 400);
        }

        try {
            $response = Http::withToken($token)
                ->timeout(5)
                ->get("https://graph.facebook.com/v19.0/{$waba->phone_number_id}/whatsapp_business_profile?fields=profile_picture_url");

            if ($response->successful()) {
                $data = $response->json();
                
                // Meta response profile_picture_url is typically nested in the first element of data
                $profilePictureUrl = null;
                if (isset($data['data'][0]['profile_picture_url'])) {
                    $profilePictureUrl = $data['data'][0]['profile_picture_url'];
                }

                if ($profilePictureUrl) {
                    // Download the image content
                    $imgResponse = Http::timeout(10)->get($profilePictureUrl);
                    if ($imgResponse->successful()) {
                        $imageContent = $imgResponse->body();
                        $filename = "waba_dp_{$waba->id}.jpg";
                        \Illuminate\Support\Facades\File::put($destinationPath . '/' . $filename, $imageContent);
                        
                        $waba->profile_picture_url = 'uploads/waba_dps/' . $filename;
                        $waba->save();

                        return response()->json([
                            'status' => true,
                            'message' => 'Profile picture synced from Meta successfully!',
                            'profile_picture_url' => asset($waba->profile_picture_url)
                        ]);
                    }
                }
                
                return response()->json([
                    'status' => false,
                    'message' => 'This account does not have a profile picture set on WhatsApp Business.'
                ], 400);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to fetch from Meta API: ' . ($response->json('error.message') ?? 'Unknown API error.')
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Sync failed: Connection to Meta API failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Upload custom DP from computer.
     */
    public function uploadDp(Request $request, $id): JsonResponse
    {
        $userId = Auth::id();
        $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $filename = "waba_dp_{$waba->id}." . $extension;

        $destinationPath = public_path('uploads/waba_dps');
        if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
            \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true);
        }

        // Clean up old DP file if exists with different extensions
        $existingFiles = glob($destinationPath . "/waba_dp_{$waba->id}.*");
        foreach ($existingFiles as $ef) {
            if (file_exists($ef)) {
                @unlink($ef);
            }
        }

        // Move the new file
        $file->move($destinationPath, $filename);
        
        $waba->profile_picture_url = 'uploads/waba_dps/' . $filename;
        $waba->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile picture uploaded successfully!',
            'profile_picture_url' => asset($waba->profile_picture_url)
        ]);
    }

    /**
     * Generate a unique verification token.
     */
    protected function generateUniqueVerifyToken(): string
    {
        do {
            $token = Str::random(32);
        } while (WhatsappAccount::where('verify_token', $token)->exists());

        return $token;
    }
}
