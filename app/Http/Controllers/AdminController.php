<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ticket;
use App\Rules\MobileNumber;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function notifications(): JsonResponse
    {
        $comments = Comment::query()
            ->where('approved', '=', false)
            ->count();

        $tickets = Ticket::query()
            ->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_PENDING])
            ->count();

        return response()->json([
            'comments' => $comments,
            'tickets' => $tickets,
        ]);
    }

    /**
     * Sends an OTP to login/register user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate(['mobile' => ['required', new MobileNumber()]]);
        $this->adminService->sendOtp($request->mobile);

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

        $isValid = $this->adminService->verifyOtp($request->mobile, $request->otp);
        if ($isValid) {
            $token = $this->adminService->loginOrRegisterAdmin($request->mobile);
            return response()->json(['message' => __('otp.verified_success'), 'token' => $token]);
        }

        return response()->json(['message' => __('otp.invalid')], 400);
    }
}
