<?php

namespace App\Services;

use App\Jobs\SendSMSJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Random\RandomException;

class UserService
{
    /**
     */
    public function sendOtp($mobile, string $fullName = '-'): bool
    {
        try {
            // Generate a random 6 digit OTP
            $otp = random_int(10 ** 5, 10 ** 6 - 1);
            if (env('APP_DEBUG')) {
                $otp = '123456';
            }
            $cacheValue = $otp . '_' . $fullName;

            $expiresAt = Carbon::now()->addMinutes(2);
            cache()->set('otp_' . $mobile, $cacheValue, $expiresAt);

            // Dispatch the job to send the OTP
            SendSMSJob::dispatch($mobile, __('Login Code: :otp', ['otp' => $otp]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return false;
        }

        return true;
    }

    public function verifyOtp($mobile, $otp): string
    {
        try {
            // Retrieve the OTP from Redis
            $cachedOtp = cache()->get("otp_{$mobile}");

            if ($cachedOtp) {
                $pcs = explode('_', $cachedOtp);
                $cachedOtp = $pcs[0];
                $fullName = $pcs[1] ?? '-';
                if ($cachedOtp == $otp) {
                    return $fullName;
                }
            }
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }

        return false;
    }

    public function loginOrRegisterUser($mobile, $fullName = ''): string
    {
        /** @var User $user */
        $user = User::query()->firstOrCreate(['mobile' => $mobile]);
        if (!$user->wasRecentlyCreated) {
            $user->tokens()->delete();
        }
        $user->update(['full_name' => $fullName]);
        return $user->createToken($mobile)->plainTextToken;
    }
}
