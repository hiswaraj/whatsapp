<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'name',
    'trigger_keywords',
    'is_active',
    'canvas_data',
    'compiled_data'
])]
class Flow extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trigger_keywords' => 'array',
            'compiled_data' => 'array',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get the user that owns this flow.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
