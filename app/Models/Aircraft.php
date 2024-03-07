<?php

namespace App\Models;

use App\Core\Contracts\Presentable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aircraft extends Model implements Presentable
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'serial_num',
        'reg_code',
        'effectivity',
        'owner',
        'manuf_date',
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
    protected $casts = [];

    /**
     * A description of the entire PHP function.
     *
     * @return  Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * A description of the entire PHP function.
     *
     * @return  Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manuals(): HasMany
    {
        return $this->hasMany(Manual::class);
    }

    /**
     * A description of the entire PHP function.
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query The query object.
     * @param  string  $type The type of aircraft.
     * @return mixed
     */
    public function scopeType(Builder $query, $type)
    {
        return $query->where('type', '=', $type);
    }

    /**
     * A description of the entire PHP function.
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query The query object.
     * @param  array  $dates The array of dates to filter between manufactured datess.
     * @return mixed The modified query object.
     */
    public function scopeBetweenManufDate(Builder $query, $dates)
    {
        return $query->whereBetween('manuf_date', $dates);
    }

    /**
     * Retrieves the display name of the model.
     *
     * @return Attribute The display name of the model.
     */
    public function displayName(): Attribute
    {
        return Attribute::get(fn () => "{$this->type}-S/{$this->serial_num}-R/{$this->reg_code}");
    }

    /**
     * Retrieves the path of the model.
     *
     * @return Attribute The path of the model.
     */
    public function path(): Attribute
    {
        return Attribute::get(fn () => "/aircraft/{$this->id}");
    }
}
