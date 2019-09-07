<?php

namespace App\Http\Controllers;

use App\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Show the roles management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $this->authorize('viewAny', 'App\Role');

        $roles = Role::orderBy('created_at', 'asc')->paginate();

        return view('role.index', ['roles' => $roles]);
    }

    /**
     * Show the role form to create a role.
     * 
     * @param $request Incoming request.
     */
    public function create(Request $request)
    {
        $this->authorize('create', 'App\Role');

        if ($request->isMethod('get'))
        {
            $permissions = \App\Permission::orderBy('created_at', 'asc')->get();

            return view('role.form', [
                'permissions' => $permissions,
                'redirect' => route('role.index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'permissions' => 'required|array|min:1',
                'permissions.*' => 'required|numeric|distinct',
            ]);

            $role = new Role($data);

            $role->save();
            
            $role->permissions()->attach(\App\Permission::whereIn('id', $data['permissions'])->get());

            return view('result', [
                'message' => __('Role was created successfully.'),
                'redirect' => route('role.index')
            ]);
        }
    }

    /**
     * Show the role form to update a role.
     * 
     * @param $request Incoming request.
     * @param $role Role id.
     */
    public function update(Request $request, $role)
    {
        $role = Role::lockForUpdate()->with('permissions')->findOrFail($role);

        $this->authorize('update', $role);

        if ($request->isMethod('get'))
        {
            $permissions = \App\Permission::orderBy('created_at', 'asc')->get();

            return view('role.form', [
                'role' => $role,
                'role_permissions' => $role->permissions->pluck('id')->toArray(),
                'permissions' => $permissions,
                'redirect' => route('role.index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'permissions' => 'required|array|min:1',
                'permissions.*' => 'required|numeric|distinct',
            ]);

            $role->fill($data)->save();
            
            $role->permissions()->sync(\App\Permission::whereIn('id', $data['permissions'])->get());

            return view('result', [
                'message' => __('Role was updated successfully.'),
                'redirect' => route('role.index')
            ]);
        }
    }

    /**
     * Delete a role.
     * 
     * @param $request Incoming request.
     * @param $role Role id.
     */
    public function delete(Request $request, $role)
    {
        $role = Role::findOrFail($role);

        $this->authorize('delete', $role);

        $role->delete();

        return view('result', [
            'message' => __('Role was deleted successfully.'),
            'redirect' => route('role.index')
        ]);
    }
}
