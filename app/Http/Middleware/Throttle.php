<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class Throttle
{
    protected array $scopes = [
        'otp' => 5,
    ];

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        if (!array_key_exists($scope, $this->scopes)) {
            return response()->json('Invalid Scope.', Response::HTTP_BAD_REQUEST);
        }

        $maxAttempts = $this->scopes[$scope];
        $ipKey = "{$scope}|ip|" . $request->ip();
        $mobileKey = "{$scope}|mobile|" . $request->input('mobile');

        if (
            RateLimiter::tooManyAttempts($ipKey, $maxAttempts) ||
            RateLimiter::tooManyAttempts($mobileKey, $maxAttempts)
        ) {
            return response()->json('Too Many Attempts.', Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($ipKey);
        RateLimiter::hit($mobileKey);

        return $next($request);
    }
}
