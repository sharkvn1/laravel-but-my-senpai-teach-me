<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param UserService $userService
     */
    public function __construct(
        protected UserService $userService
    ) {
        $this->userService = $userService;
    }

    /**
     * List all users
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function all(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->userService->getAll($data);
        return $this->responseSuccess(UserResource::collection($result));
    }

    /**
     * List all users
     *
     * @param  UserRequest $request
     * @return JsonResponse
     */
    public function detail(UserRequest $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->userService->getDetail($data);
        return $this->responseSuccess(UserResource::make($result));
    }
}
