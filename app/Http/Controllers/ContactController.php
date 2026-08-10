<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Imports\ContactsImport;
use App\Exports\ContactsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    /**
     * Display a listing of the contacts.
     */
    public function index(): View
    {
        $userId = Auth::id();
        
        // Get all contacts and groups for this tenant user
        $contacts = Contact::where('user_id', $userId)
            ->where('is_temporary', false)
            ->with('groups')
            ->orderBy('name', 'asc')
            ->get();
            
        $groups = ContactGroup::where('user_id', $userId)
            ->orderBy('name', 'asc')
            ->get();

        return view('user.contacts.index', compact('contacts', 'groups'));
    }

    /**
     * Store a newly created contact.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:contact_groups,id',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        $normalizedMobile = Contact::normalizePhoneNumber($validated['mobile_number']);

        // Check duplicate phone number for this tenant using flexible matching
        $existing = Contact::findByMobile($userId, $validated['mobile_number']);

        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'A contact with this mobile number already exists (' . $existing->name . ').'
            ], 422);
        }

        // Process tags
        $tags = [];
        if (!empty($validated['tags'])) {
            $tags = array_map('trim', explode(',', $validated['tags']));
        }

        // Store avatar
        $avatarUrl = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension();
            $filename = "contact_avatar_" . uniqid() . '.' . $extension;

            $destinationPath = public_path('uploads/contact_avatars');
            if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
                \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $avatarUrl = 'uploads/contact_avatars/' . $filename;
        }

        $contact = Contact::create([
            'user_id' => $userId,
            'name' => $validated['name'],
            'mobile_number' => $normalizedMobile,
            'email' => $validated['email'],
            'tags' => $tags,
            'notes' => $validated['notes'],
            'avatar_url' => $avatarUrl
        ]);

        // Attach groups
        if (!empty($validated['group_ids'])) {
            $contact->groups()->sync($validated['group_ids']);
        }

        return response()->json([
            'status' => true,
            'message' => 'Contact created successfully!'
        ]);
    }

    /**
     * Update the specified contact.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $userId = Auth::id();
        $contact = Contact::where('user_id', $userId)->findOrFail($id);

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:contact_groups,id',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        $normalizedMobile = Contact::normalizePhoneNumber($validated['mobile_number']);

        // Check duplicate phone number for this tenant (excluding current contact)
        $existing = Contact::findByMobile($userId, $validated['mobile_number']);

        if ($existing && $existing->id != $id) {
            return response()->json([
                'status' => false,
                'message' => 'Another contact with this mobile number already exists (' . $existing->name . ').'
            ], 422);
        }

        // Process tags
        $tags = [];
        if (!empty($validated['tags'])) {
            $tags = array_map('trim', explode(',', $validated['tags']));
        }

        // Update avatar
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension();

            if ($contact->avatar_url && !str_contains($contact->avatar_url, 'contact_avatar_sync_')) {
                $oldPath = public_path($contact->avatar_url);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $filename = "contact_avatar_" . uniqid() . '.' . $extension;

            $destinationPath = public_path('uploads/contact_avatars');
            if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
                \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $contact->avatar_url = 'uploads/contact_avatars/' . $filename;
        }

        $contact->update([
            'name' => $validated['name'],
            'mobile_number' => $normalizedMobile,
            'email' => $validated['email'],
            'tags' => $tags,
            'notes' => $validated['notes'],
            'avatar_url' => $contact->avatar_url
        ]);

        // Sync groups
        if (isset($validated['group_ids'])) {
            $contact->groups()->sync($validated['group_ids']);
        } else {
            $contact->groups()->detach();
        }

        return response()->json([
            'status' => true,
            'message' => 'Contact updated successfully!'
        ]);
    }

    /**
     * Remove the specified contact.
     */
    public function destroy($id): JsonResponse
    {
        $userId = Auth::id();
        $contact = Contact::where('user_id', $userId)->findOrFail($id);
        $contact->delete();

        return response()->json([
            'status' => true,
            'message' => 'Contact deleted successfully!'
        ]);
    }

    /**
     * Bulk CSV/Excel importer.
     */
    public function import(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:8192',
            'group_id' => 'nullable|exists:contact_groups,id'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $groupId = $request->input('group_id') ? (int)$request->input('group_id') : null;
            $import = new ContactsImport($groupId);
            Excel::import($import, $file);

            return response()->json([
                'status' => true,
                'message' => "Spreadsheet imported successfully. Imported: {$import->imported}, Skipped/Duplicates: {$import->skipped}."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Download sample contacts CSV template.
     */
    public function downloadSample(): StreamedResponse
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=contacts_sample.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['name', 'mobile_number', 'email', 'group_name', 'tags', 'notes']);
            fputcsv($file, ['John Doe', '+15550192831', 'john@example.com', 'Beta Testers', 'lead', 'Software Developer']);
            fputcsv($file, ['Jane Smith', '+919876543210', 'jane@example.com', 'VIP Customers', 'vip', 'Enterprise Client']);
            fputcsv($file, ['Alex Rivera', '+15550149204', 'alex@example.com', 'Beta Testers', 'tester', 'QA Engineer']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download contacts list as CSV.
     */
    public function export()
    {
        return Excel::download(new ContactsExport, 'contacts_export.csv');
    }

    /**
     * Sync display picture (DP) of a contact.
     */
    public function syncDp($id): JsonResponse
    {
        $userId = Auth::id();
        $contact = Contact::where('user_id', $userId)->findOrFail($id);

        // If contact has a custom uploaded avatar, return it directly
        if ($contact->avatar_url && !str_contains($contact->avatar_url, 'contact_avatar_sync_')) {
            return response()->json([
                'status' => true,
                'message' => 'Contact display picture synced!',
                'avatar_url' => asset($contact->avatar_url)
            ]);
        }

        // Clean any old dummy Unsplash avatar path
        if ($contact->avatar_url && str_contains($contact->avatar_url, 'contact_avatar_sync_')) {
            $oldPath = public_path($contact->avatar_url);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $contact->avatar_url = null;
            $contact->save();
        }

        // Generate clean initial avatar URL for this contact name
        $initialAvatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($contact->name) . '&background=6366f1&color=fff&size=200&bold=true';

        return response()->json([
            'status' => true,
            'message' => 'Contact display picture synced with clean avatar!',
            'avatar_url' => $initialAvatarUrl
        ]);
    }
}
