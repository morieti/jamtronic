<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle(Request $request, Closure $next, $guard = Admin::ROLE_SUPER_ADMIN)
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $roles = explode('|', $guard);
        if ($admin && in_array($admin->role, $roles)) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
    }
}
