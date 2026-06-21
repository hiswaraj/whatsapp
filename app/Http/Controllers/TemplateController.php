<?php

namespace App\Http\Controllers;

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

class TemplateController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $wabas = WhatsappAccount::where('user_id', $userId)
            ->where('status', true)
            ->get();

        $query = Template::where('user_id', $userId);

        // Filters
        if ($request->filled('waba_id')) {
            $query->where('whatsapp_account_id', $request->waba_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('meta_template_id', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('created_at', 'desc')->get();

        return view('user.templates.index', compact('templates', 'wabas'));
    }

    /**
     * Store a newly created template in database and push to Meta API (or simulate).
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id,user_id,' . $userId,
            'name' => ['required', 'string', 'max:512', 'regex:/^[a-z0-9_]+$/'],
            'language' => 'required|string|max:10',
            'category' => 'required|string|in:UTILITY,MARKETING,AUTHENTICATION',
            'components' => 'required|array',
        ], [
            'name.regex' => 'The template name must only contain lowercase alphanumeric characters and underscores.'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($validated['whatsapp_account_id']);
        $token = $waba->meta_access_token;
        $isMock = str_starts_with($token, 'mock_');

        // Check for duplicates locally first
        $exists = Template::where('user_id', $userId)
            ->where('whatsapp_account_id', $waba->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'A template with this name already exists for this WhatsApp Account.'
            ], 422);
        }

        // Meta Create payload
        $payload = [
            'name' => $validated['name'],
            'category' => $validated['category'],
            'language' => $validated['language'],
            'components' => $validated['components']
        ];

        if ($isMock) {
            // Mock API Response
            usleep(800000); // simulation delay
            $metaTemplateId = 'mock_tpl_' . Str::random(16);

            $template = Template::create([
                'user_id' => $userId,
                'whatsapp_account_id' => $waba->id,
                'meta_template_id' => $metaTemplateId,
                'name' => $validated['name'],
                'language' => $validated['language'],
                'category' => $validated['category'],
                'status' => 'APPROVED', // approved instantly in mock mode for utility
                'components' => $validated['components']
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Simulated template created and approved successfully!',
                'template' => $template
            ]);
        }

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->post("https://graph.facebook.com/v19.0/{$waba->whatsapp_business_account_id}/message_templates", $payload);

            if ($response->successful()) {
                $resData = $response->json();
                $metaTemplateId = $resData['id'] ?? 'unknown_id';
                $status = $resData['status'] ?? 'PENDING';

                $template = Template::create([
                    'user_id' => $userId,
                    'whatsapp_account_id' => $waba->id,
                    'meta_template_id' => $metaTemplateId,
                    'name' => $validated['name'],
                    'language' => $validated['language'],
                    'category' => $validated['category'],
                    'status' => $status,
                    'components' => $validated['components']
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Template created and submitted to Meta successfully!',
                    'template' => $template
                ]);
            } else {
                $err = $response->json('error.message') ?? 'Unknown error occurred from Meta Cloud API.';
                return response()->json([
                    'status' => false,
                    'message' => 'Meta API Error: ' . $err
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Template creation failure: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to create template due to server connection error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a template from database and Meta API (or simulate).
     */
    public function destroy(int $id): JsonResponse
    {
        $userId = Auth::id();
        $template = Template::where('user_id', $userId)->findOrFail($id);
        $waba = $template->whatsappAccount;

        $token = $waba->meta_access_token;
        $isMock = str_starts_with($token, 'mock_');

        if ($isMock) {
            usleep(600000); // delay
            $template->delete();

            return response()->json([
                'status' => true,
                'message' => 'Simulated template deleted successfully.'
            ]);
        }

        try {
            // Meta delete endpoint deletes templates by name
            $response = Http::withToken($token)
                ->timeout(10)
                ->delete("https://graph.facebook.com/v19.0/{$waba->whatsapp_business_account_id}/message_templates", [
                    'name' => $template->name
                ]);

            if ($response->successful()) {
                $template->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Template deleted from Meta and local database successfully.'
                ]);
            } else {
                // If Meta doesn't find it, we can fallback and still allow deleting it locally
                $err = $response->json('error.message') ?? 'Unknown delete error.';
                if ($response->status() === 404 || str_contains(strtolower($err), 'not found') || str_contains(strtolower($err), 'does not exist')) {
                    $template->delete();
                    return response()->json([
                        'status' => true,
                        'message' => 'Template not found on Meta, but removed from local database.'
                    ]);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Meta API delete failed: ' . $err
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Template deletion failure: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete template due to connection error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Synchronize templates from Meta API.
     */
    public function sync(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id,user_id,' . $userId
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($request->whatsapp_account_id);
        $token = $waba->meta_access_token;
        $isMock = str_starts_with($token, 'mock_');

        if ($isMock) {
            usleep(1200000); // 1.2 second simulation delay

            // Generate some mock templates to show to the user
            $mockTemplatesData = [
                [
                    'id' => 'mock_tpl_hello_world',
                    'name' => 'hello_world',
                    'category' => 'UTILITY',
                    'language' => 'en_US',
                    'status' => 'APPROVED',
                    'components' => [
                        [
                            'type' => 'HEADER',
                            'format' => 'TEXT',
                            'text' => 'Greeting Notification'
                        ],
                        [
                            'type' => 'BODY',
                            'text' => 'Hello {{1}}, welcome to our WhatsApp messaging service! Let us know if you need assistance.'
                        ],
                        [
                            'type' => 'FOOTER',
                            'text' => 'WhatsApp SaaS Inc'
                        ]
                    ]
                ],
                [
                    'id' => 'mock_tpl_marketing_offer',
                    'name' => 'summer_promotion',
                    'category' => 'MARKETING',
                    'language' => 'en_US',
                    'status' => 'APPROVED',
                    'components' => [
                        [
                            'type' => 'HEADER',
                            'format' => 'IMAGE'
                        ],
                        [
                            'type' => 'BODY',
                            'text' => 'Hi {{1}}! Use code {{2}} to get 25% off all summer items in our catalog. Valid until next Sunday.'
                        ],
                        [
                            'type' => 'FOOTER',
                            'text' => 'Reply STOP to unsubscribe'
                        ],
                        [
                            'type' => 'BUTTONS',
                            'buttons' => [
                                [
                                    'type' => 'URL',
                                    'text' => 'Shop Collection',
                                    'url' => 'https://example.com/summer-sale'
                                ],
                                [
                                    'type' => 'PHONE_NUMBER',
                                    'text' => 'Call Sales',
                                    'phone_number' => '+15550192831'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 'mock_tpl_otp_code',
                    'name' => 'verification_code',
                    'category' => 'AUTHENTICATION',
                    'language' => 'en_US',
                    'status' => 'APPROVED',
                    'components' => [
                        [
                            'type' => 'BODY',
                            'text' => 'Your authentication code is {{1}}. This code will expire in 10 minutes. Please do not share this code.'
                        ],
                        [
                            'type' => 'BUTTONS',
                            'buttons' => [
                                [
                                    'type' => 'COPY_CODE',
                                    'text' => 'Copy Code',
                                    'code' => '123456'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            // Clean up existing templates for this WABA and insert the mock templates
            Template::where('user_id', $userId)
                ->where('whatsapp_account_id', $waba->id)
                ->delete();

            $syncedCount = 0;
            foreach ($mockTemplatesData as $tpl) {
                Template::create([
                    'user_id' => $userId,
                    'whatsapp_account_id' => $waba->id,
                    'meta_template_id' => $tpl['id'],
                    'name' => $tpl['name'],
                    'language' => $tpl['language'],
                    'category' => $tpl['category'],
                    'status' => $tpl['status'],
                    'components' => $tpl['components']
                ]);
                $syncedCount++;
            }

            return response()->json([
                'status' => true,
                'message' => "Synced successfully! {$syncedCount} mock templates imported for WABA '{$waba->display_name}'."
            ]);
        }

        try {
            $response = Http::withToken($token)
                ->timeout(15)
                ->get("https://graph.facebook.com/v19.0/{$waba->whatsapp_business_account_id}/message_templates", [
                    'limit' => 200
                ]);

            if ($response->successful()) {
                $metaTemplates = $response->json('data') ?? [];
                
                // Track synced template IDs
                $syncedMetaIds = [];

                foreach ($metaTemplates as $tpl) {
                    $metaTemplateId = $tpl['id'] ?? null;
                    if (!$metaTemplateId) {
                        continue;
                    }

                    $syncedMetaIds[] = $metaTemplateId;

                    Template::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'whatsapp_account_id' => $waba->id,
                            'meta_template_id' => $metaTemplateId
                        ],
                        [
                            'name' => $tpl['name'],
                            'language' => $tpl['language'],
                            'category' => $tpl['category'],
                            'status' => $tpl['status'] ?? 'APPROVED',
                            'components' => $tpl['components'] ?? []
                        ]
                    );
                }

                // Delete local templates that are no longer present on Meta's server
                $deletedCount = Template::where('user_id', $userId)
                    ->where('whatsapp_account_id', $waba->id)
                    ->whereNotIn('meta_template_id', $syncedMetaIds)
                    ->delete();

                $totalCount = Template::where('user_id', $userId)
                    ->where('whatsapp_account_id', $waba->id)
                    ->count();

                return response()->json([
                    'status' => true,
                    'message' => "Synchronization complete! Verified {$totalCount} templates. Removed {$deletedCount} stale records."
                ]);
            } else {
                $err = $response->json('error.message') ?? 'Unknown sync error from Meta.';
                return response()->json([
                    'status' => false,
                    'message' => 'Sync failed: ' . $err
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Template sync failure: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
