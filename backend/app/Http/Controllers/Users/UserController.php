<?php

namespace App\Http\Controllers\Users;

use App\Application\Users\ChangeUserStatus;
use App\Application\Users\CreateUser;
use App\Application\Users\Data\ChangeUserStatusData;
use App\Application\Users\Data\CreateUserData;
use App\Application\Users\Data\UpdateUserData;
use App\Application\Users\ListUsers;
use App\Application\Users\UpdateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ChangeUserStatusRequest;
use App\Http\Requests\Users\CreateUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\Users\UserSummaryResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class UserController extends Controller
{
    public function index(ListUsers $listUsers): AnonymousResourceCollection
    {
        return UserSummaryResource::collection($listUsers->handle());
    }

    public function store(CreateUserRequest $request, CreateUser $createUser): JsonResponse
    {
        $user = $createUser->handle(new CreateUserData(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            roles: $request->input('roles'),
        ));

        return (new UserSummaryResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $updateUser): UserSummaryResource
    {
        $summary = $updateUser->handle($user, new UpdateUserData(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->filled('password')
                ? $request->string('password')->toString()
                : null,
            roles: $request->has('roles') ? $request->input('roles') : null,
        ));

        return new UserSummaryResource($summary);
    }

    public function changeStatus(
        ChangeUserStatusRequest $request,
        User $user,
        ChangeUserStatus $changeUserStatus,
    ): UserSummaryResource {
        $summary = $changeUserStatus->handle($user, new ChangeUserStatusData(
            isActive: $request->boolean('is_active'),
        ));

        return new UserSummaryResource($summary);
    }
}
