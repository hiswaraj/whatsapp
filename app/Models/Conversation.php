<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'whatsapp_account_id',
    'contact_id',
    'last_message_at',
    'unread_count'
])]
class Conversation extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'unread_count' => 'integer',
        ];
    }

    /**
     * Get the user that owns this conversation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the WhatsApp Business Account associated with this conversation.
     */
    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsappAccount::class);
    }

    /**
     * Get the contact associated with this conversation.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the messages within this conversation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
