<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date_from',
        'date_to',
        'user_id',
        'media_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'is_confirmed' => 'boolean',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
