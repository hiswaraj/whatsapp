<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'company', 'user_type', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'integer',
        ];
    }

    /**
     * Get the WhatsApp Accounts owned by this user.
     */
    public function whatsappAccounts()
    {
        return $this->hasMany(WhatsappAccount::class);
    }

    /**
     * Get the contacts managed by this user.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the contact groups created by this user.
     */
    public function contactGroups()
    {
        return $this->hasMany(ContactGroup::class);
    }

    /**
     * Get the conversations under this user's account.
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get all messages under this user's account.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get all campaigns created by this user.
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get all synced templates for this user.
     */
    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    /**
     * Get all media library items uploaded by this user.
     */
    public function mediaLibrary()
    {
        return $this->hasMany(MediaLibrary::class);
    }
}
