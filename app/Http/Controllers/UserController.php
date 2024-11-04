<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\MobileNumber;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(): JsonResponse
    {
        $users = User::with(['userAddresses', 'userAddresses.region', 'userAddresses.city'])->get();
        return response()->json($users);
    }

    public function search(Request $request): JsonResponse
    {
        $name = $request->input('name', '');
        $mobile = $request->input('mobile', '');
        $email = $request->input('email', '');
        $nc = $request->input('national_code', '');

        $perPage = (int)$request->input('size', 20);
        $page = (int)$request->input('page', 1);

        $filters = $request->except(['name', 'mobile', 'email', 'national_code', 'size', 'page'], []);

        $filterQuery = $this->arrangeFilters($filters);

        $searchQuery = trim("{$name} {$mobile} {$email} {$nc}");

        $users = User::search('')
            ->when($searchQuery, function ($search) use ($searchQuery, $name, $mobile, $email, $nc) {
                $search->query(function ($query) use ($searchQuery, $name, $mobile, $email, $nc) {
                    $query
                        ->where('full_name', 'LIKE', "%{$name}%")
                        ->where('mobile', 'LIKE', "%{$mobile}%")
                        ->where('email', 'LIKE', "%{$email}%")
                        ->where('national_code', 'LIKE', "%{$nc}%");
                });
            })
            ->when($filterQuery, function ($search, $filterQuery) {
                $search->options['filter'] = $filterQuery;
                $search->raw($filterQuery);
            })
            ->paginate($perPage, 'page', $page);

        $users = $users->jsonSerialize();
        unset($users['data']['totalHits']);

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
            return response()->json(['message' => __('otp.verified_success'), 'token' => $token]);
        }

        return response()->json(['message' => __('otp.invalid')], 400);
    }

    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->last_order_status = $user->lastOrder() ? $user->lastOrder()->status : '';
        return response()->json($user);
    }

    public function adminGet(int $id): JsonResponse
    {
        /** @var User $user */
        $user = User::query()->with(['userAddresses', 'userAddresses.region', 'userAddresses.city'])->findOrFail($id);
        $user->last_order_status = $user->lastOrder() ? $user->lastOrder()->status : '';
        return response()->json($user);
    }

    public function createUserByAdmin(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'mobile' => ['required', new MobileNumber(), 'unique:users,mobile'],
                'full_name' => 'nullable|string|max:255',
                'national_code' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email',
                'dob' => 'nullable|integer',
                'mob' => 'nullable|integer',
                'yob' => 'nullable|integer',
            ]);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }

        $user = new User($request->all());
        try {
            $user->save();
            return response()->json($user);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    public function adminUpdate(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = User::query()->findOrFail($id);

        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'national_code' => 'nullable|string|max:255',
            'status_active' => 'nullable|boolean',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'dob' => 'nullable|integer',
            'mob' => 'nullable|integer',
            'yob' => 'nullable|integer',
        ]);

        $user->update($request->all());

        if (!$user->status_active) {
            $user->tokens()->delete();
        }

        return response()->json($user);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $id = $user->id;

        try {
            $request->validate([
                'full_name' => 'nullable|string|max:255',
                'national_code' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
                'dob' => 'nullable|integer',
                'mob' => 'nullable|integer',
                'yob' => 'nullable|integer',
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }

        $user->update($request->except('wallet_balance'));
        return response()->json($user);
    }

    public function getWalletTransactions(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        return response()->json($user->walletHistory);
    }
}
