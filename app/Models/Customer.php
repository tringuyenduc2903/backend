<?php

namespace App\Models;

use App\Enums\CustomerGender;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends User implements MustVerifyEmail
{
    use CrudTrait;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use SwitchTimezoneTrait;
    use TwoFactorAuthenticatable;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'birthday',
        'gender',
        'email',
        'phone_number',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_number_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'password' => 'hashed',
        'remember_token' => 'hashed',
    ];

    protected $appends = [
        'gender_preview',
        'timezone_preview',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function identifications(): HasMany
    {
        return $this->hasMany(Identification::class);
    }

    public function socials(): HasMany
    {
        return $this->hasMany(Social::class)->latest();
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class)->latest();
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class)->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function getGenderPreviewAttribute(): ?string
    {
        return is_null($this->gender)
            ? null
            : CustomerGender::valueForKey($this->gender);
    }

    public function getTimezonePreviewAttribute(): string
    {
        return timezone_identifiers_list()[$this->timezone];
    }
}
