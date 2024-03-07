<?php

namespace App\Models;

use App\Core\Contracts\Localizeable;
use App\Core\Contracts\Presentable;
use App\Core\Models\Traits\HasAvatar;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, Localizeable, Presentable
{
    use Authenticatable, Authorizable, CanResetPassword, HasApiTokens, HasAvatar, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Permissions guard name
     *
     * @var string
     */
    public $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'updated_at',
        // 'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isSuperAdmin(): Attribute
    {
        return Attribute::get(fn () => $this->hasRole('super-admin'));
    }


    public function isAdmin(): Attribute
    {
        return Attribute::get(fn () => $this->hasRole('regular-admin'));
    }

    /**
     * Retrieves the settings associated with the current object.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    /**
     * Retrieves the settings associated with the current object.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class);
    }

    /**
     * Retrieves the settings associated with the current object.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'assignee_id');
    }

    /**
     * Get the user's preferred locale.
     */
    public function preferredLocale(): string
    {
        return $this->settings->locale ?? config('app.locale');
    }

    /**
     * Get the user time format
     */
    public function getLocalTimeFormat(): string
    {
        return $this->settings->time_format ?? config('core.time_format');
    }

    /**
     * Get the user date format
     */
    public function getLocalDateFormat(): string
    {
        return $this->settings->date_format ?? config('core.date_format');
    }

    /**
     * Get the user timezone
     */
    public function getUserTimezone(): string
    {
        return $this->settings->timezone ?? config('app.timezone');
    }

    /**
     * Latest 15 user notifications help relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function latestFifteenNotifications()
    {
        return $this->notifications()->take(15);
    }

    public function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->name);
    }

    public function path(): Attribute
    {
        return Attribute::get(fn () => "/{$this->table}/{$this->id}");
    }
}
