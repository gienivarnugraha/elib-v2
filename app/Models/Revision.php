<?php

namespace App\Models;

use App\Core\Contracts\Presentable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Revision extends Model implements HasMedia
{
    use HasFactory,
        InteractsWithMedia,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'body',
        'user_id',
        'index',
        'index_date',
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
        'is_closed' => 'boolean',
        'is_canceled' => 'boolean',
        'index_date' => 'date',
    ];

    public function getLatestRevision()
    {
        return $this->orderBy('id', 'desc')->latest()->first();
    }

    protected function revisable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
