<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'conversation_id',
    'campaign_id',
    'whatsapp_account_id',
    'meta_message_id',
    'type',
    'message_type',
    'body',
    'media_path',
    'media_mime_type',
    'meta_template_id',
    'status',
    'error_message',
    'template_params'
])]
class Message extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'template_params' => 'array',
        ];
    }
    /**
     * Get the user that owns this message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the conversation that contains this message.
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the campaign that sent this message.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the WhatsApp Business Account that transmitted/received this message.
     */
    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsappAccount::class);
    }
}
