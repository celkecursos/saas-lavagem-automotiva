<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Atribuição de permissions a um papel (task-23, seção 2) — lista TODAS
 * as permissions com indicador Liberado/Bloqueado; update() faz TOGGLE.
 * Bloqueada pro papel "Super Admin" (Gate::before já libera tudo pra
 * ele, não há o que atribuir).
 */
class RolePermissionController extends Controller
{
    public function index(Role $role): View
    {
        abort_if($role->name === 'Super Admin', 403, 'Super Admin já tem acesso a tudo — não há o que atribuir.');

        $permissions = Permission::orderBy('name')->get();
        $grantedIds = $role->permissions()->pluck('id')->all();

        return view('admin.role-permissions.index', compact('role', 'permissions', 'grantedIds'));
    }

    public function update(Role $role, Permission $permission): RedirectResponse
    {
        abort_if($role->name === 'Super Admin', 403);

        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
        } else {
            $role->givePermissionTo($permission);
        }

        return redirect()->route('role-permissions.index', $role);
    }
}
