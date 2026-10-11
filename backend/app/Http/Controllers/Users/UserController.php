<?php

namespace App\Http\Controllers\Users;

use App\Application\Users\ListUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserSummaryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class UserController extends Controller
{
    public function index(ListUsers $listUsers): AnonymousResourceCollection
    {
        return UserSummaryResource::collection($listUsers->handle());
    }
}
