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
use Illuminate\Support\Facades\Log;
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

        // If starting immediately, trigger Artisan process command instantly
        if ($status === 'processing') {
            try {
                Artisan::call('campaigns:process');
            } catch (\Exception $e) {
                try {
                    Artisan::queue('campaigns:process');
                } catch (\Exception $ex) {
                    Log::warning('Campaign dispatch warning: ' . $ex->getMessage());
                }
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
            'action' => 'required|string|in:pause,resume,cancel,process_now,resend_failed'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $action = $request->input('action');

        if ($action === 'resend_failed') {
            $failedMessages = Message::where('campaign_id', $campaign->id)
                ->where('status', 'failed')
                ->get();

            if ($failedMessages->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No failed messages found in this campaign.'
                ], 422);
            }

            foreach ($failedMessages as $msg) {
                $msg->update([
                    'status' => 'pending',
                    'error_message' => null
                ]);
            }

            $campaign->update([
                'failed_count' => max(0, $campaign->failed_count - $failedMessages->count()),
                'status' => 'processing'
            ]);

            try {
                Artisan::call('campaigns:process');
                $msg = 'Resend triggered for ' . $failedMessages->count() . ' failed message(s)!';
            } catch (\Exception $e) {
                try {
                    Artisan::queue('campaigns:process');
                    $msg = 'Resend queued for processing.';
                } catch (\Exception $ex) {
                    $msg = 'Failed to execute resend command: ' . $ex->getMessage();
                }
            }
        } elseif ($action === 'process_now') {
            if (in_array($campaign->status, ['cancelled', 'failed'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot process a cancelled or failed campaign.'
                ], 422);
            }

            if ($campaign->status === 'completed') {
                $hasPending = Message::where('campaign_id', $campaign->id)->where('status', 'pending')->exists();
                if (!$hasPending) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Campaign is already completed and has no pending messages to dispatch.'
                    ], 422);
                }
            }

            if ($campaign->status !== 'processing') {
                $campaign->update(['status' => 'processing']);
            }

            try {
                Artisan::call('campaigns:process');
                $msg = '1-Click send triggered successfully! Messages are being dispatched now.';
            } catch (\Exception $e) {
                try {
                    Artisan::queue('campaigns:process');
                    $msg = 'Process command queued for dispatch.';
                } catch (\Exception $ex) {
                    Log::warning('Manual dispatch error: ' . $ex->getMessage());
                    $msg = 'Failed to execute background dispatch: ' . $ex->getMessage();
                }
            }
        } elseif ($action === 'pause') {
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
                Artisan::call('campaigns:process');
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
     * Resend a single failed message by ID.
     */
    public function resendSingleMessage(int $messageId): JsonResponse
    {
        $userId = Auth::id();
        $message = Message::where('user_id', $userId)
            ->where('id', $messageId)
            ->firstOrFail();

        $campaign = $message->campaign;

        // Reset message to pending
        $message->update([
            'status' => 'pending',
            'error_message' => null
        ]);

        if ($campaign) {
            if ($campaign->failed_count > 0) {
                $campaign->decrement('failed_count');
            }
            if ($campaign->status !== 'processing') {
                $campaign->update(['status' => 'processing']);
            }
        }

        try {
            Artisan::call('campaigns:process');
        } catch (\Exception $e) {
            try {
                Artisan::queue('campaigns:process');
            } catch (\Exception $ex) {
                Log::warning('Resend single message error: ' . $ex->getMessage());
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Message queued for immediate re-sending!'
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
