<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the users management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $this->authorize('viewAny', 'App\User');

        $users = User::orderBy('created_at', 'asc')->paginate();

        return view('user.index', ['users' => $users]);
    }

    /**
     * Show the user form to create a user.
     * 
     * @param $request Incoming request.
     */
    public function create(Request $request)
    {
        $this->authorize('create', 'App\User');

        $redirect = $request->filled('redirect') ? $request->redirect : route('index');

        if ($request->isMethod('get'))
        {
            $roles = \App\Role::orderBy('created_at', 'asc')->get();

            return view('user.form', [
                'roles' => $roles,
                'redirect' => route('user.index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'photo' => 'nullable|image|max:1024',
                'roles' => 'required|array|min:1',
                'roles.*' => 'required|numeric|distinct',
            ]);

            $user = new User([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            if ($request->has('photo')) $user->photo = $request->photo->store('images/avatars', 'public');

            $user->save();
            
            $user->roles()->attach(\App\Role::whereIn('id', $data['roles'])->get());

            return view('result', [
                'message' => __('User was created successfully.'),
                'redirect' => route('user.index')
            ]);
        }
    }

    /**
     * Show the user form to update a user.
     * 
     * @param $request Incoming request.
     * @param $user User id.
     */
    public function update(Request $request, $user)
    {
        $user = User::lockForUpdate()->with('roles')->findOrFail($user);

        $this->authorize('update', $user);

        if ($request->isMethod('get'))
        {
            $roles = \App\Role::orderBy('created_at', 'asc')->get();

            return view('user.form', [
                'user' => $user,
                'user_roles' => $user->roles->pluck('id')->toArray(),
                'roles' => $roles,
                'redirect' => route('user.index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $rules = array(
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
                'password' => ['nullable', 'string', 'min:8'],
                'photo' => 'nullable|image|max:1024',
            );

            if (!$user->is_admin)
            {
                $rules['roles'] = ['required', 'array', 'min:1'];
                $rules['roles.*'] = ['required', 'numeric', 'distinct'];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
            {
                return redirect()
                    ->route('user.update', [
                        'user' => $user->id
                    ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $user_data = array(
                'name' => $data['name'],
                'email' => $data['email'],
            );

            if (!empty($data['password']))
                $user_data['password'] = Hash::make($data['password']);

            $user->fill($user_data);

            if ($request->has('no_photo') && $request->no_photo == 'on')
            {
                if (Storage::disk('public')->exists($user->photo)) Storage::disk('public')->delete($user->photo);

                $user->photo = null;
            }
            else if ($request->has('photo'))
            {
                if ($user->photo && Storage::disk('public')->exists($user->photo)) Storage::disk('public')->delete($user->photo);

                $user->photo = $request->photo->store('images/avatars', 'public');
            } 

            $user->save();
            
            if (!$user->is_admin) $user->roles()->sync(\App\Role::whereIn('id', $data['roles'])->get());

            return view('result', [
                'message' => __('User was updated successfully.'),
                'redirect' => route('user.index')
            ]);
        }
    }

    /**
     * Delete a user.
     * 
     * @param $request Incoming request.
     * @param $user User id.
     */
    public function delete(Request $request, $user)
    {
        $user = User::findOrFail($user);

        $this->authorize('delete', $user);

        if ($user->photo)
        {
            $photo_path = 'public/' . $user->photo;

            if (Storage::exists($photo_path)) Storage::delete($photo_path);
        }

        $user->delete();

        return view('result', [
            'message' => __('User was deleted successfully.'),
            'redirect' => route('user.index')
        ]);
    }

    /**
     * Show the user profile to update information.
     * 
     * @param $request Incoming request.
     */
    public function profile(Request $request)
    {
        if ($request->isMethod('get'))
        {
            return view('user.profile', [
                'redirect' => route('index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $user = User::findOrFail(auth()->user()->id);

            $rules = array(
                'password' => ['bail', 'required', 'string', 'min:8', function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) return $fail(__('The current password is incorrect.'));
                }],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
                'photo' => ['nullable', 'image', 'max:1024'],
                'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
            );

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
            {
                return redirect()
                    ->route('user.profile')
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $user_data = array(
                'name' => $data['name'],
                'email' => $data['email'],
            );

            if (!empty($data['new_password']))
                $user_data['password'] = Hash::make($data['new_password']);

            $user->fill($user_data);

            if ($request->has('no_photo') && $request->no_photo == 'on')
            {
                if (Storage::disk('public')->exists($user->photo)) Storage::disk('public')->delete($user->photo);

                $user->photo = null;
            }
            else if ($request->has('photo'))
            {
                if ($user->photo && Storage::disk('public')->exists($user->photo)) Storage::disk('public')->delete($user->photo);

                $user->photo = $request->photo->store('images/avatars', 'public');
            } 

            $user->save();

            return view('result', [
                'message' => __('Your profile was updated successfully.'),
                'redirect' => route('index')
            ]);
        }
    }

    /**
     * Show the user interaction result.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function result()
    {
        return view('auth.result');
    }
}
