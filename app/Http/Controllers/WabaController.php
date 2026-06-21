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
            'verify_token' => 'nullable|string|max:255',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        // Enforce only one WABA setting per tenant user
        $hasAccount = WhatsappAccount::where('user_id', $userId)->exists();
        if ($hasAccount) {
            return response()->json([
                'status' => false,
                'message' => 'Only a single WhatsApp Business Account (WABA) setting can be added.'
            ], 422);
        }

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
            'verify_token' => $validated['verify_token'] ?? Str::random(32),
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
            'verify_token' => 'nullable|string|max:255',
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
            'verify_token' => $validated['verify_token'] ?? $waba->verify_token,
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
}
