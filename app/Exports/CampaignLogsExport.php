<?php

namespace App\Exports;

use App\Models\Message;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CampaignLogsExport implements FromCollection, WithHeadings, WithMapping
{
    protected int $campaignId;

    /**
     * Create a new export instance.
     */
    public function __construct(int $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    /**
     * Fetch messages for this campaign.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Message::where('campaign_id', $this->campaignId)
            ->with('conversation.contact')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Export column headers.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Recipient Name',
            'Mobile Number',
            'Status',
            'Sent At',
            'Delivered At / Updated At',
            'Message Content',
            'Delivery Notes / Error'
        ];
    }

    /**
     * Map each row of database values to export format.
     *
     * @param mixed $msg
     * @return array
     */
    public function map($msg): array
    {
        return [
            $msg->conversation->contact->name ?? 'Unknown Contact',
            $msg->conversation->contact->mobile_number ?? '',
            ucfirst($msg->status),
            $msg->created_at->format('Y-m-d H:i:s'),
            $msg->updated_at->format('Y-m-d H:i:s'),
            $msg->body ?? '',
            $msg->error_message ?? ''
        ];
    }
}
