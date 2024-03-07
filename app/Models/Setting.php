<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date_format',
        'time_format',
        'first_day_of_week',
        'currency',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'first_day_of_week' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
