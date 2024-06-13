<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Model
{
    use HasApiTokens, HasFactory;

    public const ROLE_UNASSIGNED = 'unassigned';
    public const ROLE_SUPPORT = 'support';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const roles = [
        self::ROLE_UNASSIGNED,
        self::ROLE_SUPPORT,
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN
    ];

    protected $fillable = [
        'full_name',
        'mobile',
        'role',
        'last_login',
    ];
}
