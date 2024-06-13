<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserAuth
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();

        if ($user) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
    }
}
