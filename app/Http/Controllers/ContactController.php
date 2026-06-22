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

        // Check duplicate phone number for this tenant
        $exists = Contact::where('user_id', $userId)
            ->where('mobile_number', $validated['mobile_number'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'A contact with this mobile number already exists.'
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
            'mobile_number' => $validated['mobile_number'],
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

        // Check duplicate phone number for this tenant (excluding current contact)
        $exists = Contact::where('user_id', $userId)
            ->where('mobile_number', $validated['mobile_number'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Another contact with this mobile number already exists.'
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

            if ($contact->avatar_url) {
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
            'mobile_number' => $validated['mobile_number'],
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
            $import = new ContactsImport();
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

        $mockUrls = [
            "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1488161628813-04466f872be2?auto=format&fit=crop&w=150&q=80",
            "https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=150&q=80"
        ];

        $selectedUrl = $mockUrls[$contact->id % count($mockUrls)];

        $destinationPath = public_path('uploads/contact_avatars');
        if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
            \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true);
        }

        try {
            $imgResponse = \Illuminate\Support\Facades\Http::timeout(10)->get($selectedUrl);
            if ($imgResponse->successful()) {
                if ($contact->avatar_url) {
                    $oldPath = public_path($contact->avatar_url);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $filename = "contact_avatar_sync_{$contact->id}.jpg";
                \Illuminate\Support\Facades\File::put($destinationPath . '/' . $filename, $imgResponse->body());

                $contact->avatar_url = 'uploads/contact_avatars/' . $filename;
                $contact->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Contact display picture synced successfully!',
                    'avatar_url' => asset($contact->avatar_url)
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to sync contact DP: ' . $e->getMessage());
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to sync display picture due to connection error.'
        ], 500);
    }
}
