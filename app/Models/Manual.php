<?php

namespace App\Models;

use App\Core\Contracts\Presentable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manual extends Model implements Presentable
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'aircraft_id',
        'type',
        'part_number',
        'lib_call',
        'subject',
        'volume',
        'vendor',
        'caplist',
        'collector',
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
        'caplist' => 'boolean'
    ];

    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisable');
    }

    public function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->part_number);
    }

    public function path(): Attribute
    {
        return Attribute::get(fn () => "/manuals/{$this->id}");
    }
}
