<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleCollection;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Role::class);
        $role = Role::all();
        return response()->json([
            'message' => 'Success',
            'data' => new RoleCollection($role),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $this->authorize('create', Role::class);
        $validated = $request->validated();
        $role = Role::create($validated);

        return response()->json([
            'message' => 'Success',
            'data' => new RoleResource($role),
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $this->authorize('view', $role);
        if (!$role) {
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Success',
            'data' => new RoleResource($role),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->authorize('update', $role);
        if (!$role) {
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }

        $validated = $request->validated();
        $role->update($validated);

        return response()->json([
            'message' => 'Success',
            'data' => new RoleResource($role),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);
        if (!$role) {
            return response()->json([
                'message' => 'Failed'
            ], 404);
        }

        $role->delete();
        return response()->json([
            'message' => 'Success',
        ], 200);
    }

    // {
    //     "name": "sample role"
    // }
}