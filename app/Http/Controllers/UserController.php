<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserWalletHistory;
use App\Rules\MobileNumber;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Sends an OTP to login/register user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => ['required', new MobileNumber()],
            'full_name' => 'required|string|max:255',
        ]);
        $fullName = $request->get('full_name');
        $this->userService->sendOtp($request->mobile, $fullName);

        return response()->json(['message' => __('otp.sent_success')]);
    }

    /**
     * Verifies sent OTP
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => ['required', new MobileNumber()],
            'otp' => 'required'
        ]);

        $fullName = $this->userService->verifyOtp($request->mobile, $request->otp);
        if ($fullName) {
            $token = $this->userService->loginOrRegisterUser($request->mobile, $fullName);
            return response()->json(['message' => __('otp.verified_success'), 'token' => $token]);
        }

        return response()->json(['message' => __('otp.invalid')], 400);
    }

    public function show(int $id): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        if ($user->id != $id) {
            return response()->json('Access Denied!', Response::HTTP_FORBIDDEN);
        }
        return response()->json($user);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'national_code' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'dob' => 'nullable|integer',
            'mob' => 'nullable|integer',
            'yob' => 'nullable|integer',
        ]);

        /** @var User $user */
        $user = auth()->user();
        if ($user->id != $id) {
            return response()->json('Access Denied!', Response::HTTP_FORBIDDEN);
        }

        $user->update($request->all());
        return response()->json($user);
    }

    public function getWalletTransactions(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        return response()->json($user->walletHistory);
    }
}
