<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Template;
use App\Models\WhatsappAccount;
use App\Imports\QuickBroadcastImport;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuickBroadcastController extends Controller
{
    /**
     * Show the quick broadcast workspace.
     */
    public function index(): View
    {
        $userId = Auth::id();

        // Approved WABAs
        $wabas = WhatsappAccount::where('user_id', $userId)
            ->where('status', true)
            ->get();

        // Approved Templates
        $templates = Template::where('user_id', $userId)
            ->where('status', 'APPROVED')
            ->orderBy('name', 'asc')
            ->get();

        return view('user.quick-broadcast.index', compact('wabas', 'templates'));
    }

    /**
     * Download sample Excel/CSV template.
     */
    public function downloadSample(): StreamedResponse
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=quick_broadcast_sample.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, ['mobile_number', 'recipient_name', 'variable_1', 'variable_2']);

            // Sample Row 1
            fputcsv($file, ['+15550192831', 'John Doe', 'June Promo', '25% OFF']);

            // Sample Row 2
            fputcsv($file, ['+919876543210', 'Jane Smith', 'Summer Offer', 'Free Delivery']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Upload and parse the Excel/CSV file to extract headers using maatwebsite/excel.
     */
    public function parse(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:8192'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $originalExtension = $file->getClientOriginalExtension();

            // Ensure unique path inside local storage
            $storedName = 'qb_' . Str::random(16) . '.' . $originalExtension;
            $path = $file->storeAs('quick_broadcasts', $storedName);

            $absolutePath = Storage::path($path);

            // Parse using Maatwebsite\Excel
            try {
                $sheets = Excel::toArray(new QuickBroadcastImport, $absolutePath);
            } catch (Exception $e) {
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file is corrupt or is not a valid Excel/CSV spreadsheet. Details: ' . $e->getMessage()
                ], 422);
            }

            $sheet = $sheets[0] ?? [];
            if (empty($sheet)) {
                Storage::delete($path);
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded spreadsheet is empty.'
                ], 422);
            }

            // Extract first row as headers
            $rawHeaders = array_shift($sheet);
            if (empty($rawHeaders)) {
                Storage::delete($path);
                return response()->json([
                    'status' => false,
                    'message' => 'No headers found in the first row of the spreadsheet.'
                ], 422);
            }

            // Clean headers: trim whitespace and filter out empty columns
            $headers = [];
            foreach ($rawHeaders as $index => $val) {
                $cleanVal = trim($val ?? '');
                if ($cleanVal !== '') {
                    $headers[] = $cleanVal;
                }
            }

            if (empty($headers)) {
                Storage::delete($path);
                return response()->json([
                    'status' => false,
                    'message' => 'No valid column headers found in the spreadsheet.'
                ], 422);
            }

            // Count valid non-empty data rows
            $rowsCount = 0;
            foreach ($sheet as $row) {
                $hasContent = false;
                foreach ($row as $val) {
                    if (trim($val ?? '') !== '') {
                        $hasContent = true;
                        break;
                    }
                }
                if ($hasContent) {
                    $rowsCount++;
                }
            }

            if ($rowsCount === 0) {
                Storage::delete($path);
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded spreadsheet contains no data rows.'
                ], 422);
            }

            return response()->json([
                'status' => true,
                'message' => 'File uploaded and parsed successfully.',
                'filepath' => $path,
                'headers' => $headers,
                'total_rows' => $rowsCount
            ]);
        } catch (Exception $e) {
            Log::error('Error parsing broadcast file: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to parse file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process configuration and create quick broadcast campaign with pending messages.
     */
    public function send(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'filepath' => 'required|string',
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id,user_id,' . $userId,
            'template_id' => 'required|exists:templates,id,user_id,' . $userId,
            'campaign_name' => 'required|string|max:255',
            'save_contacts' => 'nullable|boolean',
            'phone_column' => 'required|string',
            'name_column' => 'nullable|string',
            'variable_mappings' => 'nullable|array',
            'variable_values' => 'nullable|array',
            'header_attachment' => 'nullable|string'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();
        $absolutePath = Storage::path($validated['filepath']);

        if (!file_exists($absolutePath)) {
            return response()->json([
                'status' => false,
                'message' => 'Uploaded file not found or has expired. Please upload again.'
            ], 422);
        }

        try {
            // Parse using Maatwebsite\Excel
            try {
                $sheets = Excel::toArray(new QuickBroadcastImport, $absolutePath);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'The spreadsheet could not be read: ' . $e->getMessage()
                ], 422);
            }

            $sheet = $sheets[0] ?? [];
            $rawHeaders = array_shift($sheet);

            // Clean headers
            $headers = array_map(function ($h) {
                return trim($h ?? '');
            }, $rawHeaders);

            $rows = [];
            foreach ($sheet as $row) {
                $hasContent = false;
                foreach ($row as $val) {
                    if (trim($val ?? '') !== '') {
                        $hasContent = true;
                        break;
                    }
                }
                if ($hasContent) {
                    $rows[] = array_map(function ($val) {
                        return trim($val ?? '');
                    }, $row);
                }
            }

            // Create column name to index map
            $headerMap = array_flip($headers);
            $phoneColName = $validated['phone_column'];
            $nameColName = $validated['name_column'] ?? null;

            if (!isset($headerMap[$phoneColName])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mapped phone column "' . $phoneColName . '" not found in spreadsheet.'
                ], 422);
            }

            $phoneColIndex = $headerMap[$phoneColName];
            $nameColIndex = ($nameColName && isset($headerMap[$nameColName])) ? $headerMap[$nameColName] : null;

            $template = Template::where('user_id', $userId)->findOrFail($validated['template_id']);
            $waba = WhatsappAccount::where('user_id', $userId)->findOrFail($validated['whatsapp_account_id']);

            // Determine variable mappings
            $variableMappings = $validated['variable_mappings'] ?? [];

            // Verify mapping headers exist (only for mapped columns)
            foreach ($variableMappings as $paramIdx => $colHeader) {
                if ($colHeader !== null && $colHeader !== '') {
                    if (!isset($headerMap[$colHeader])) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Mapped variable column "' . $colHeader . '" not found in spreadsheet.'
                        ], 422);
                    }
                }
            }

            // Create a merged representation of variables (columns or custom fixed strings)
            $templateVariables = [];
            $variableValues = $request->input('variable_values') ?? [];
            $allVarIndexes = array_unique(array_merge(array_keys($variableMappings), array_keys($variableValues)));
            sort($allVarIndexes);
            foreach ($allVarIndexes as $paramIdx) {
                if (isset($variableMappings[$paramIdx]) && $variableMappings[$paramIdx] !== '') {
                    $templateVariables[] = 'Column: ' . $variableMappings[$paramIdx];
                } else {
                    $templateVariables[] = 'Custom: ' . ($variableValues[$paramIdx] ?? '');
                }
            }

            // 1. Create Campaign
            $campaign = Campaign::create([
                'user_id' => $userId,
                'whatsapp_account_id' => $waba->id,
                'template_id' => $template->id,
                'contact_group_id' => null,
                'name' => $validated['campaign_name'],
                'status' => 'processing',
                'scheduled_at' => null,
                'template_variables' => $templateVariables,
                'total_contacts' => count($rows),
                'sent_count' => 0,
                'delivered_count' => 0,
                'read_count' => 0,
                'failed_count' => 0
            ]);

            $isTemporary = !($validated['save_contacts'] ?? false);

            foreach ($rows as $row) {
                $phone = preg_replace('/[^0-9+]/', '', $row[$phoneColIndex] ?? '');
                if (empty($phone)) {
                    continue;
                }

                $name = 'Recipient';
                if ($nameColIndex !== null && !empty($row[$nameColIndex])) {
                    $name = $row[$nameColIndex];
                } else {
                    $name = 'Guest ' . substr($phone, -4);
                }

                // 2. Find or Create Contact dynamically
                $contact = Contact::where('user_id', $userId)
                    ->where('mobile_number', $phone)
                    ->first();

                if ($contact) {
                    if (!$isTemporary && $contact->is_temporary) {
                        $contact->update(['is_temporary' => false]);
                    }
                } else {
                    $tags = ['quick-broadcast'];
                    if ($isTemporary) {
                        $tags[] = 'temporary';
                    }
                    $contact = Contact::create([
                        'user_id' => $userId,
                        'name' => $name,
                        'mobile_number' => $phone,
                        'is_temporary' => $isTemporary,
                        'tags' => $tags
                    ]);
                }

                // 3. Resolve Conversation
                $conversation = Conversation::firstOrCreate([
                    'user_id' => $userId,
                    'whatsapp_account_id' => $waba->id,
                    'contact_id' => $contact->id
                ], [
                    'last_message_at' => now(),
                    'unread_count' => 0
                ]);

                // 4. Resolve variable parameters for this specific row
                $templateParams = [];
                foreach ($allVarIndexes as $paramIdx) {
                    $val = '';
                    if (isset($variableMappings[$paramIdx]) && $variableMappings[$paramIdx] !== '') {
                        $colHeader = $variableMappings[$paramIdx];
                        $colIdx = $headerMap[$colHeader] ?? null;
                        $val = ($colIdx !== null) ? ($row[$colIdx] ?? '') : '';
                    } elseif (isset($variableValues[$paramIdx]) && $variableValues[$paramIdx] !== '') {
                        $val = $variableValues[$paramIdx];
                    }

                    // Replace shortcuts
                    $val = str_replace('{{name}}', $contact->name, $val);
                    $val = str_replace('{{mobile}}', $contact->mobile_number, $val);
                    $templateParams[] = $val;
                }

                // 5. Compile body text locally
                $bodyText = '';
                foreach ($template->components as $comp) {
                    if ($comp['type'] === 'BODY') {
                        $bodyText = $comp['text'];
                    }
                }
                foreach ($templateParams as $idx => $paramVal) {
                    $bodyText = str_replace('{{' . ($idx + 1) . '}}', $paramVal, $bodyText);
                }

                // 6. Pre-create the pending message
                Message::create([
                    'user_id' => $userId,
                    'conversation_id' => $conversation->id,
                    'campaign_id' => $campaign->id,
                    'whatsapp_account_id' => $waba->id,
                    'type' => 'outgoing',
                    'message_type' => 'template',
                    'body' => $bodyText,
                    'meta_template_id' => $template->meta_template_id,
                    'status' => 'pending',
                    'template_params' => $templateParams,
                    'media_path' => $request->input('header_attachment')
                ]);
            }

            // Cleanup the uploaded temp file
            Storage::delete($validated['filepath']);

            // Trigger campaign queue processor
            try {
                Artisan::queue('campaigns:process');
            } catch (Exception $e) {
                // Fail-safe
            }

            return response()->json([
                'status' => true,
                'message' => 'Quick Broadcast scheduled and starting to send!',
                'redirect_url' => route('campaigns.show', $campaign->id)
            ]);
        } catch (Exception $e) {
            Log::error('Error processing quick broadcast: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while scheduling the broadcast: ' . $e->getMessage()
            ], 500);
        }
    }
}
