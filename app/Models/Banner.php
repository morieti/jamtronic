<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    public const string TYPE_TOP = 'top';
    public const string TYPE_BOTTOM = 'bottom';

    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'image',
        'link',
        'status',
        'type'
    ];

    public function setActive(): bool
    {
        $this->status = 'active';
        return $this->save();
    }

    public function setInactive(): bool
    {
        $this->status = 'inactive';
        return $this->save();
    }

    public function setStatus($status): void
    {
        if ($status) {
            $this->setActive();
        } else {
            $this->setInactive();
        }
    }
}
