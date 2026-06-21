<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'name'
])]
class ContactGroup extends Model
{
    /**
     * Get the user that owns this contact group.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the contacts belonging to this group.
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_contact_group');
    }

    /**
     * Get the campaigns targeting this contact group.
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
}
