<?php

namespace App\Events;

use App\Models\Order;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

class WalletBalanceUpdated
{
    use SerializesModels;

    public User $user;
    public $order;
    public string $title = '';
    public string $subtitle = '';
    public int $amount = 0;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function setData(string $title, string $subtitle, int $amount): static
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->amount = $amount;

        return $this;
    }
}
