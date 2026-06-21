<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'whatsapp_account_id',
    'meta_template_id',
    'name',
    'language',
    'category',
    'status',
    'components'
])]
class Template extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'components' => 'array',
        ];
    }

    /**
     * Get the user that owns this template.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the WhatsApp Business Account associated with this template.
     */
    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsappAccount::class);
    }
}
