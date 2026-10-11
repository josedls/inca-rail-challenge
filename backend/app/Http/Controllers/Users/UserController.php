<?php

namespace App\Http\Controllers\Users;

use App\Application\Users\CreateUser;
use App\Application\Users\Data\CreateUserData;
use App\Application\Users\ListUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\CreateUserRequest;
use App\Http\Resources\Users\UserSummaryResource;
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
}
