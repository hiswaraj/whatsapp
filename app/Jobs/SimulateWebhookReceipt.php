<?php

namespace App\Jobs;

use App\Models\Message;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SimulateWebhookReceipt implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected int $messageId;
    protected string $status;

    /**
     * Create a new job instance.
     */
    public function __construct(int $messageId, string $status)
    {
        $this->messageId = $messageId;
        $this->status = $status;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $message = Message::find($this->messageId);

            if ($message && in_array($message->status, ['sent', 'delivered'])) {
                $message->update(['status' => $this->status]);

                $campaign = $message->campaign;
                if ($campaign) {
                    $campaign->update([
                        'sent_count' => Message::where('campaign_id', $campaign->id)->whereIn('status', ['sent', 'delivered', 'read'])->count(),
                        'delivered_count' => Message::where('campaign_id', $campaign->id)->whereIn('status', ['delivered', 'read'])->count(),
                        'read_count' => Message::where('campaign_id', $campaign->id)->where('status', 'read')->count(),
                        'failed_count' => Message::where('campaign_id', $campaign->id)->where('status', 'failed')->count(),
                    ]);
                }

                Log::info("Simulated webhook status update for message ID {$this->messageId} to {$this->status}");
            }
        } catch (Exception $e) {
            Log::error("Error executing simulated webhook job: " . $e->getMessage());
        }
    }
}
