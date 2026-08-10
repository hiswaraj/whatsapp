<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Imports\GroupsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactGroupController extends Controller
{
    /**
     * Display a listing of the groups.
     */
    public function index(): View
    {
        $userId = Auth::id();

        // Get groups with count of assigned contacts and eager loaded relation
        $groups = ContactGroup::where('user_id', $userId)
            ->with('contacts')
            ->withCount('contacts')
            ->orderBy('name', 'asc')
            ->get();

        // Get contacts list for assignment modals
        $contacts = Contact::where('user_id', $userId)
            ->where('is_temporary', false)
            ->orderBy('name', 'asc')
            ->get();

        return view('user.groups.index', compact('groups', 'contacts'));
    }

    /**
     * Store a newly created group.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        // Ensure name is unique for this tenant
        $exists = ContactGroup::where('user_id', $userId)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'A group with this name already exists.'
            ], 422);
        }

        ContactGroup::create([
            'user_id' => $userId,
            'name' => $validated['name']
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Group created successfully!'
        ]);
    }

    /**
     * Update the specified group.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $userId = Auth::id();
        $group = ContactGroup::where('user_id', $userId)->findOrFail($id);

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        // Ensure name is unique for this tenant (excluding current group)
        $exists = ContactGroup::where('user_id', $userId)
            ->where('name', $validated['name'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Another group with this name already exists.'
            ], 422);
        }

        $group->update([
            'name' => $validated['name']
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Group updated successfully!'
        ]);
    }

    /**
     * Remove the specified group.
     */
    public function destroy($id): JsonResponse
    {
        $userId = Auth::id();
        $group = ContactGroup::where('user_id', $userId)->findOrFail($id);
        $group->delete();

        return response()->json([
            'status' => true,
            'message' => 'Group deleted successfully!'
        ]);
    }

    /**
     * Assign contacts to a group.
     */
    public function assignContacts(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'group_id' => 'required|exists:contact_groups,id',
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contacts,id'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $group = ContactGroup::where('user_id', $userId)->findOrFail($request->group_id);

        // Verify that the requested contacts belong to the tenant user and are not temporary
        $contactsCount = Contact::where('user_id', $userId)
            ->where('is_temporary', false)
            ->whereIn('id', $request->contact_ids)
            ->count();

        if ($contactsCount !== count($request->contact_ids)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid contacts selected.'
            ], 403);
        }

        // Sync without detaching existing contacts in the group
        $group->contacts()->syncWithoutDetaching($request->contact_ids);

        return response()->json([
            'status' => true,
            'message' => 'Contacts assigned to group successfully!'
        ]);
    }

    /**
     * Remove contacts from a group.
     */
    public function removeContacts(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'group_id' => 'required|exists:contact_groups,id',
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contacts,id'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $group = ContactGroup::where('user_id', $userId)->findOrFail($request->group_id);

        // Detach the selected contact IDs from the pivot table
        $group->contacts()->detach($request->contact_ids);

        return response()->json([
            'status' => true,
            'message' => 'Contacts removed from group successfully!'
        ]);
    }

    /**
     * Download sample contact groups CSV template.
     */
    public function downloadSample(): StreamedResponse
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=groups_sample.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['group_name', 'contact_name', 'mobile_number', 'email']);
            fputcsv($file, ['Beta Testers', 'John Doe', '+15550192831', 'john@example.com']);
            fputcsv($file, ['VIP Customers', 'Jane Smith', '+919876543210', 'jane@example.com']);
            fputcsv($file, ['Marketing Leads', '', '', '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk CSV/Excel group importer.
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
            $import = new GroupsImport();
            Excel::import($import, $file);

            return response()->json([
                'status' => true,
                'message' => "Groups spreadsheet imported successfully. Created: {$import->groupsCreated} group(s), Processed: {$import->groupsProcessed} group(s), Assigned: {$import->contactsImported} contact(s)."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 422);
        }
    }
}
