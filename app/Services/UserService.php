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
    public function sendOtp($mobile): bool
    {
        try {
            // Generate a random 6 digit OTP
            $otp = random_int(10 ** 5, 10 ** 6 - 1);
            if (env('APP_DEBUG')) {
                $otp = '123456';
            }

            $expiresAt = Carbon::now()->addMinutes(2);
            cache()->set('otp_' . $mobile, $otp, $expiresAt);

            // Dispatch the job to send the OTP
            SendSMSJob::dispatch($mobile, __('Login Code: :otp', ['otp' => $otp]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return false;
        }

        return true;
    }

    public function verifyOtp($mobile, $otp): bool
    {
        try {
            // Retrieve the OTP from Redis
            $cachedOtp = cache()->get("otp_{$mobile}");

            if ($cachedOtp && $cachedOtp == $otp) {
                return true;
            }
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }
        return false;
    }

    public function loginOrRegisterUser($mobile): string
    {
        /** @var User $user */
        $user = User::query()->firstOrCreate(['mobile' => $mobile]);
        if (!$user->wasRecentlyCreated) {
            $user->tokens()->delete();
        }
        return $user->createToken($mobile)->plainTextToken;
    }
}
