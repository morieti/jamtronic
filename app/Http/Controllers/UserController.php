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

    public function index(): JsonResponse
    {
        $users = User::all();
        return response()->json($users);
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
            $data = $this->userService->loginOrRegisterUser($request->mobile, $fullName);
            $token = $data['token'];
            $userID = $data['user_id'];
            return response()->json(['message' => __('otp.verified_success'), 'token' => $token, 'user_id' => $userID]);
        }

        return response()->json(['message' => __('otp.invalid')], 400);
    }

    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->last_order_status = $user->lastOrder()->status;
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
