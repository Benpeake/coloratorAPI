<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Palette extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'hex_colors' => 'array',
    ];

    protected $fillable = [
        'name',
        'hex_colors',
        'public',
        'votes',
        'user_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
