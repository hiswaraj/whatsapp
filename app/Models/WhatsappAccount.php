<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'display_name',
    'meta_access_token',
    'phone_number_id',
    'whatsapp_business_account_id',
    'meta_app_id',
    'verify_token',
    'status',
    'profile_picture_url'
])]
class WhatsappAccount extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta_access_token' => 'encrypted',
            'status' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this WhatsApp Business Account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the conversations associated with this account.
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get the templates synced for this account.
     */
    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    /**
     * Get the campaigns dispatched from this account.
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
}
