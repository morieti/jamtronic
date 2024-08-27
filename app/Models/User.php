<?php

namespace App\Models;

use App\Events\WalletBalanceUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

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
        'wallet_balance',
        'dob',
        'mob',
        'yob',
    ];

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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function lastOrder(): Order
    {
        return $this->orders()->orderBy('updated_at', 'desc')->first();
    }
}
