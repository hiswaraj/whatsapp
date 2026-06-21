<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Campaign;
use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Fetch key KPI counters for the user.
     */
    public function getMetrics(int $userId): array
    {
        $today = Carbon::today();

        $totalContacts = Contact::where('user_id', $userId)->where('is_temporary', false)->count();
        $totalGroups = ContactGroup::where('user_id', $userId)->count();
        $totalConversations = Conversation::where('user_id', $userId)->count();
        $activeAccounts = WhatsappAccount::where('user_id', $userId)->where('status', true)->count();

        // Messages Sent Today (Outgoing messages)
        $sentToday = Message::where('user_id', $userId)
            ->where('type', 'outgoing')
            ->whereDate('created_at', $today)
            ->count();

        // Messages Delivered Today (Outgoing and status is delivered or read)
        $deliveredToday = Message::where('user_id', $userId)
            ->where('type', 'outgoing')
            ->whereIn('status', ['delivered', 'read'])
            ->whereDate('created_at', $today)
            ->count();

        // Messages Read Today (Outgoing and status is read)
        $readToday = Message::where('user_id', $userId)
            ->where('type', 'outgoing')
            ->where('status', 'read')
            ->whereDate('created_at', $today)
            ->count();

        // Messages Failed Today
        $failedToday = Message::where('user_id', $userId)
            ->where('type', 'outgoing')
            ->where('status', 'failed')
            ->whereDate('created_at', $today)
            ->count();

        return [
            'total_contacts' => $totalContacts,
            'total_groups' => $totalGroups,
            'total_conversations' => $totalConversations,
            'active_accounts' => $activeAccounts,
            'sent_today' => $sentToday,
            'delivered_today' => $deliveredToday,
            'read_today' => $readToday,
            'failed_today' => $failedToday,
        ];
    }

    /**
     * Fetch message volumes grouped by Day, Week, and Month.
     */
    public function getMessageChartData(int $userId): array
    {
        // 1. Daily Trend (Last 7 Days)
        $dailyLabels = [];
        $dailySent = [];
        $dailyReceived = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dailyLabels[] = $date->format('D, M d');

            $dailySent[] = Message::where('user_id', $userId)
                ->where('type', 'outgoing')
                ->whereDate('created_at', $date->toDateString())
                ->count();

            $dailyReceived[] = Message::where('user_id', $userId)
                ->where('type', 'incoming')
                ->whereDate('created_at', $date->toDateString())
                ->count();
        }

        // 2. Weekly Trend (Last 4 Weeks)
        $weeklyLabels = [];
        $weeklySent = [];
        $weeklyReceived = [];

        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
            $weeklyLabels[] = 'Week ' . $startOfWeek->format('W') . ' (' . $startOfWeek->format('M d') . ')';

            $weeklySent[] = Message::where('user_id', $userId)
                ->where('type', 'outgoing')
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->count();

            $weeklyReceived[] = Message::where('user_id', $userId)
                ->where('type', 'incoming')
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->count();
        }

        // 3. Monthly Trend (Last 6 Months)
        $monthlyLabels = [];
        $monthlySent = [];
        $monthlyReceived = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $date->format('M Y');

            $monthlySent[] = Message::where('user_id', $userId)
                ->where('type', 'outgoing')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $monthlyReceived[] = Message::where('user_id', $userId)
                ->where('type', 'incoming')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return [
            'daily' => [
                'labels' => $dailyLabels,
                'sent' => $dailySent,
                'received' => $dailyReceived,
            ],
            'weekly' => [
                'labels' => $weeklyLabels,
                'sent' => $weeklySent,
                'received' => $weeklyReceived,
            ],
            'monthly' => [
                'labels' => $monthlyLabels,
                'sent' => $monthlySent,
                'received' => $monthlyReceived,
            ],
        ];
    }

    /**
     * Fetch recent activity updates (latest messages).
     */
    public function getRecentActivity(int $userId, int $limit = 5)
    {
        return Message::where('user_id', $userId)
            ->with(['conversation.contact'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Fetch campaign execution summaries.
     */
    public function getCampaignSummaries(int $userId, int $limit = 5)
    {
        return Campaign::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
