<?php

namespace App\Http\Controllers\Permissions;

use App\Application\Permissions\CreatePermission;
use App\Application\Permissions\Data\CreatePermissionData;
use App\Application\Permissions\Data\UpdatePermissionData;
use App\Application\Permissions\DeletePermission;
use App\Application\Permissions\ListPermissions;
use App\Application\Permissions\UpdatePermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permissions\CreatePermissionRequest;
use App\Http\Requests\Permissions\UpdatePermissionRequest;
use App\Http\Resources\Permissions\PermissionSummaryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Permission;

final class PermissionController extends Controller
{
    public function index(ListPermissions $listPermissions): AnonymousResourceCollection
    {
        return PermissionSummaryResource::collection($listPermissions->handle());
    }

    public function store(
        CreatePermissionRequest $request,
        CreatePermission $createPermission,
    ): JsonResponse {
        $permission = $createPermission->handle(new CreatePermissionData(
            name: $request->string('name')->toString(),
        ));

        return (new PermissionSummaryResource($permission))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdatePermissionRequest $request,
        Permission $permission,
        UpdatePermission $updatePermission,
    ): PermissionSummaryResource {
        $summary = $updatePermission->handle($permission, new UpdatePermissionData(
            name: $request->string('name')->toString(),
        ));

        return new PermissionSummaryResource($summary);
    }

    public function destroy(Permission $permission, DeletePermission $deletePermission): JsonResponse
    {
        $deletePermission->handle($permission);

        return response()->json(status: 204);
    }
}
