<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'whatsapp_account_id',
    'template_id',
    'contact_group_id',
    'name',
    'status',
    'scheduled_at',
    'template_variables',
    'total_contacts',
    'sent_count',
    'delivered_count',
    'read_count',
    'failed_count'
])]
class Campaign extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'total_contacts' => 'integer',
            'sent_count' => 'integer',
            'delivered_count' => 'integer',
            'read_count' => 'integer',
            'failed_count' => 'integer',
            'template_variables' => 'array'
        ];
    }

    /**
     * Get the user that owns this campaign.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the WhatsApp Business Account associated with this campaign.
     */
    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsappAccount::class);
    }

    /**
     * Get the template selected for this campaign.
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get the contact group targeted by this campaign.
     */
    public function contactGroup()
    {
        return $this->belongsTo(ContactGroup::class);
    }

    /**
     * Get the messages sent under this campaign.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
