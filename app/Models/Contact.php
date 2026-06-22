<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'name',
    'mobile_number',
    'email',
    'tags',
    'notes',
    'is_temporary',
    'avatar_url'
])]
class Contact extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_temporary' => 'boolean'
        ];
    }

    /**
     * Get the user that manages this contact.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the groups that this contact belongs to.
     */
    public function groups()
    {
        return $this->belongsToMany(ContactGroup::class, 'contact_contact_group');
    }

    /**
     * Get the conversations associated with this contact.
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
