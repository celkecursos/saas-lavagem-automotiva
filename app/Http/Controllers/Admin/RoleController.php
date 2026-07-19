<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * CRUD de papéis (task-23, seção 1, replicando o padrão do projeto
 * adm). "Super Admin" é sempre order=1 e não pode ter a ordem alterada.
 */
class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::orderBy('order')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'order' => (Role::max('order') ?? 0) + 1,
        ]);

        return redirect()->route('roles.index')->with('success', 'Papel criado.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->name === 'Super Admin', 403, 'O papel Super Admin não pode ser editado.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
        ]);

        $role->update($validated);

        return redirect()->route('roles.index')->with('success', 'Papel atualizado.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->name === 'Super Admin', 403, 'O papel Super Admin não pode ser removido.');

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Papel removido.');
    }

    public function updateOrder(Request $request): RedirectResponse
    {
        abort_if(
            Role::whereKey($request->input('role_id'))->value('name') === 'Super Admin',
            403,
            'A ordem do Super Admin não pode ser alterada.',
        );

        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];

        $roles = Role::where('name', '!=', 'Super Admin')->orderBy('order')->get();
        $role = $roles->firstWhere('id', $request->input('role_id'));
        $index = $roles->search(fn (Role $item) => $item->id === $role->id);
        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapIndex >= 0 && $swapIndex < $roles->count()) {
            $sibling = $roles[$swapIndex];
            [$roleOrder, $siblingOrder] = [$role->order, $sibling->order];
            $role->update(['order' => $siblingOrder]);
            $sibling->update(['order' => $roleOrder]);
        }

        return redirect()->route('roles.index');
    }
}
