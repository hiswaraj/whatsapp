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

    /**
     * Normalize a mobile number to standard E.164 format (+919876543210).
     */
    public static function normalizePhoneNumber(?string $number): string
    {
        if (empty($number)) {
            return '';
        }

        $cleaned = preg_replace('/[^\d+]/', '', trim($number));
        $digits = ltrim($cleaned, '+');

        if (empty($digits)) {
            return '';
        }

        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+' . $digits;
        }

        return '+' . $digits;
    }

    /**
     * Flexible lookup for existing contact by mobile number (handles +91, 91, or 10-digit variations).
     */
    public static function findByMobile(int $userId, ?string $mobileNumber): ?self
    {
        if (empty($mobileNumber)) {
            return null;
        }

        $normalized = self::normalizePhoneNumber($mobileNumber);
        $digits = preg_replace('/\D/', '', $mobileNumber);
        $last10 = (strlen($digits) >= 10) ? substr($digits, -10) : $digits;

        return self::where('user_id', $userId)
            ->where(function ($query) use ($mobileNumber, $normalized, $digits, $last10) {
                $query->where('mobile_number', $mobileNumber)
                      ->orWhere('mobile_number', $normalized)
                      ->orWhere('mobile_number', $digits)
                      ->orWhere('mobile_number', '+' . $digits);
                if (strlen($last10) >= 10) {
                    $query->orWhere('mobile_number', 'LIKE', '%' . $last10);
                }
            })
            ->first();
    }
}
