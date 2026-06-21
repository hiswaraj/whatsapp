<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\ContactGroup;
use App\Models\Message;
use App\Models\Template;
use App\Models\WhatsappAccount;
use App\Exports\CampaignLogsExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignController extends Controller
{
    /**
     * Display a listing of campaigns.
     */
    public function index(): View
    {
        $userId = Auth::id();
        $campaigns = Campaign::where('user_id', $userId)
            ->with(['contactGroup', 'template'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.campaigns.index', compact('campaigns'));
    }

    /**
     * Show the campaign creator workspace.
     */
    public function create(): View
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

        // Contact Groups
        $groups = ContactGroup::where('user_id', $userId)
            ->orderBy('name', 'asc')
            ->get();

        return view('user.campaigns.create', compact('wabas', 'templates', 'groups'));
    }

    /**
     * Store a newly created campaign.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id,user_id,' . $userId,
            'template_id' => 'required|exists:templates,id,user_id,' . $userId,
            'contact_group_id' => 'required|exists:contact_groups,id,user_id,' . $userId,
            'scheduled_at' => 'nullable|date|after:now',
            'template_variables' => 'nullable|array'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        $group = ContactGroup::where('user_id', $userId)->findOrFail($validated['contact_group_id']);
        $totalContacts = $group->contacts()->count();

        if ($totalContacts === 0) {
            return response()->json([
                'status' => false,
                'message' => 'The selected contact group contains no contacts.'
            ], 422);
        }

        $scheduledAt = null;
        $status = 'processing'; // default immediate

        if (!empty($validated['scheduled_at'])) {
            $scheduledAt = Carbon::parse($validated['scheduled_at']);
            if ($scheduledAt->isFuture()) {
                $status = 'scheduled';
            }
        }

        $campaign = Campaign::create([
            'user_id' => $userId,
            'whatsapp_account_id' => $validated['whatsapp_account_id'],
            'template_id' => $validated['template_id'],
            'contact_group_id' => $validated['contact_group_id'],
            'name' => $validated['name'],
            'status' => $status,
            'scheduled_at' => $scheduledAt,
            'template_variables' => $validated['template_variables'] ?? [],
            'total_contacts' => $totalContacts,
            'sent_count' => 0,
            'delivered_count' => 0,
            'read_count' => 0,
            'failed_count' => 0
        ]);

        // If starting immediately, trigger Artisan process command instantly in background
        if ($status === 'processing') {
            try {
                Artisan::queue('campaigns:process');
            } catch (\Exception $e) {
                // Fail-safe if queue is not configured
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Campaign created successfully!',
            'redirect_url' => route('campaigns.index')
        ]);
    }

    /**
     * Display campaign delivery analytics and sent message logs.
     */
    public function show(int $id)
    {
        $userId = Auth::id();
        $campaign = Campaign::where('user_id', $userId)
            ->with(['whatsappAccount', 'template', 'contactGroup'])
            ->findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => true,
                'campaign' => $campaign
            ]);
        }

        // Fetch paginated messages sent under this campaign
        $messages = Message::where('campaign_id', $campaign->id)
            ->with('conversation.contact')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('user.campaigns.show', compact('campaign', 'messages'));
    }

    /**
     * Perform control actions (pause, resume, cancel) on the campaign.
     */
    public function action(Request $request, int $id): JsonResponse
    {
        $userId = Auth::id();
        $campaign = Campaign::where('user_id', $userId)->findOrFail($id);

        $validation = Validator::make($request->all(), [
            'action' => 'required|string|in:pause,resume,cancel'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $action = $request->input('action');

        if ($action === 'pause') {
            if ($campaign->status !== 'processing') {
                return response()->json([
                    'status' => false,
                    'message' => 'Only active processing campaigns can be paused.'
                ], 422);
            }
            $campaign->update(['status' => 'paused']);
            $msg = 'Campaign paused successfully.';
        } elseif ($action === 'resume') {
            if (!in_array($campaign->status, ['paused', 'draft'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Only paused or draft campaigns can be resumed.'
                ], 422);
            }
            $campaign->update(['status' => 'processing']);
            $msg = 'Campaign resumed and started processing.';

            // Trigger immediate process runner
            try {
                Artisan::queue('campaigns:process');
            } catch (\Exception $e) {
                // Fail-safe
            }
        } elseif ($action === 'cancel') {
            if (in_array($campaign->status, ['completed', 'failed', 'cancelled'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Campaign has already finished or is already cancelled.'
                ], 422);
            }
            $campaign->update(['status' => 'cancelled']);
            $msg = 'Campaign cancelled successfully.';
        }

        return response()->json([
            'status' => true,
            'message' => $msg,
            'campaign_status' => $campaign->status
        ]);
    }

    /**
     * Remove the campaign from database storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $userId = Auth::id();
        $campaign = Campaign::where('user_id', $userId)->findOrFail($id);

        // Delete the campaign record
        $campaign->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campaign record deleted successfully.'
        ]);
    }

    /**
     * Download campaign transmission logs as CSV.
     */
    public function exportLogs(int $id)
    {
        $userId = Auth::id();
        $campaign = Campaign::where('user_id', $userId)->findOrFail($id);
        
        return Excel::download(new CampaignLogsExport($campaign->id), 'broadcast_logs_' . $campaign->id . '.csv');
    }
}
