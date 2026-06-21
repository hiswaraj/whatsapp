<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'filename',
    'file_path',
    'file_type',
    'file_size'
])]
class MediaLibrary extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media_library';

    /**
     * Get the user that uploaded this media asset.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
