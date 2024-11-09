<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'mobile',
        'email',
        'national_code',
        'status_active',
        'wallet_balance',
        'dob',
        'mob',
        'yob',
    ];

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return 'users_index';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (int)$this->id,
            'full_name' => $this->full_name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'national_code' => $this->national_code,
            'status_active' => (int)$this->status_active,
            'dob' => (int)$this->dob,
            'mob' => (int)$this->mob,
            'yob' => (int)$this->yob,
            'created_at' => $this->created_at
        ];
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function walletHistory(): HasMany
    {
        return $this->hasMany(UserWalletHistory::class);
    }

    public function userAddresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function lastOrder()
    {
        return $this->orders()->orderBy('updated_at', 'desc')->first();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
