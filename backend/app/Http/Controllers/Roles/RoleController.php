<?php

namespace App\Http\Controllers\Roles;

use App\Application\Roles\CreateRole;
use App\Application\Roles\Data\CreateRoleData;
use App\Application\Roles\Data\UpdateRoleData;
use App\Application\Roles\DeleteRole;
use App\Application\Roles\ListRoles;
use App\Application\Roles\UpdateRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\CreateRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Http\Resources\Roles\RoleSummaryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

final class RoleController extends Controller
{
    public function index(ListRoles $listRoles): AnonymousResourceCollection
    {
        return RoleSummaryResource::collection($listRoles->handle());
    }

    public function store(CreateRoleRequest $request, CreateRole $createRole): JsonResponse
    {
        $role = $createRole->handle(new CreateRoleData(
            name: $request->string('name')->toString(),
            permissions: $request->input('permissions', []),
        ));

        return (new RoleSummaryResource($role))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateRoleRequest $request, Role $role, UpdateRole $updateRole): RoleSummaryResource
    {
        $summary = $updateRole->handle($role, new UpdateRoleData(
            name: $request->string('name')->toString(),
            permissions: $request->input('permissions'),
        ));

        return new RoleSummaryResource($summary);
    }

    public function destroy(Role $role, DeleteRole $deleteRole): JsonResponse
    {
        $deleteRole->handle($role);

        return response()->json(status: 204);
    }
}
