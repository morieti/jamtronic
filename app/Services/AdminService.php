<?php

namespace App\Services;

use App\Models\Admin;

class AdminService extends UserService
{
    public function loginOrRegisterAdmin($mobile): string
    {
        /** @var Admin $admin */
        $admin = Admin::query()->firstOrCreate(['mobile' => $mobile]);
//        if (!$admin->wasRecentlyCreated) {
//            $admin->tokens()->delete();
//        }
        return $admin->createToken($mobile, [$admin->role])->plainTextToken;
    }
}
