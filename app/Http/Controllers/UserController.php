<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Show the users management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $this->authorize('viewAny', 'App\User');

        $users = User::orderBy('created_at', 'asc')->paginate();

        return view($this->findView('user.index'), ['users' => $users]);
    }

    /**
     * Show the user page.
     *
     * @param $user User id.
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show($user)
    {
        $user = \App\User::findOrFail($user);

        return view($this->findView('user.show'), [
            'user' => $user
        ]);
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

            return view($this->findView('user.form'), [
                'roles' => $roles,
                'timezones' => \DateTimeZone::listIdentifiers(\DateTimeZone::ALL),
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
                'bio' => 'nullable|string',
                'timezone' => 'nullable|string',
                'badge' => 'nullable|string|in:None,Moderator',
                'roles' => 'required|array|min:1',
                'roles.*' => 'required|numeric|distinct',
            ]);

            $user = new User([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'badge' => $data['badge'],
                'bio' => $data['bio'],
                'timezone' => $data['timezone']
            ]);

            if ($request->has('photo')) $user->photo = $request->photo->store('images/avatars', 'public');

            $user->save();
            
            $user->roles()->attach(\App\Role::whereIn('id', $data['roles'])->get());

            return view($this->findView('result'), [
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

            return view($this->findView('user.form'), [
                'user' => $user,
                'user_roles' => $user->roles->pluck('id')->toArray(),
                'roles' => $roles,
                'timezones' => \DateTimeZone::listIdentifiers(\DateTimeZone::ALL),
                'redirect' => $this->getRedirect($request, $user)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $rules = array(
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
                'password' => ['nullable', 'string', 'min:8'],
                'photo' => ['nullable', 'image', 'max:1024'],
                'bio' => ['nullable', 'string'],
                'timezone' => ['nullable', 'string'],
            );

            if (!$user->is_admin)
            {
                $rules['badge'] = ['nullable', 'string', 'in:None,Moderator'];
                $rules['roles'] = ['required', 'array', 'min:1'];
                $rules['roles.*'] = ['required', 'numeric', 'distinct'];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
            {
                return redirect()
                    ->route('user.update', [
                        'user' => $user->id,
                        'redirect' => $request->filled('redirect') ? $request->redirect : 'index'
                    ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $user_data = array(
                'name' => $data['name'],
                'email' => $data['email'],
                'bio' => $data['bio'],
                'timezone' => $data['timezone']
            );

            if (!$user->is_admin)
                $user_data['badge'] = $data['badge'];

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

            return view($this->findView('result'), [
                'message' => __('User was updated successfully.'),
                'redirect' => $this->getRedirect($request, $user)
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

        return view($this->findView('result'), [
            'message' => __('User was deleted successfully.'),
            'redirect' => $this->getRedirect($request, $user)
        ]);
    }

    /**
     * Ban a user.
     * 
     * @param $request Incoming request.
     * @param $user User id.
     */
    public function ban(Request $request, $user)
    {
        $user = User::findOrFail($user);

        $this->authorize('ban', $user);

        $user->is_banned = true;

        $user->save();

        return view($this->findView('result'), [
            'message' => __('User was banned successfully.'),
            'redirect' => $this->getRedirect($request, $user)
        ]);
    }

    /**
     * Unban a user.
     * 
     * @param $request Incoming request.
     * @param $user User id.
     */
    public function unban(Request $request, $user)
    {
        $user = User::findOrFail($user);

        $this->authorize('ban', $user);

        $user->is_banned = false;

        $user->save();

        return view($this->findView('result'), [
            'message' => __('User was unbanned successfully.'),
            'redirect' => $this->getRedirect($request, $user)
        ]);
    }

    /**
     * Show the users favorites page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function favorites()
    {
        $favorites = \App\Favorite::orderBy('created_at', 'asc')->paginate();

        return view($this->findView('user.favorites'), ['favorites' => $favorites]);
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
            return view($this->findView('user.profile'), [
                'redirect' => route('index'),
                'timezones' => \DateTimeZone::listIdentifiers(\DateTimeZone::ALL)
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
                'show_email' => 'required|in:on,off',
                'photo' => ['nullable', 'image', 'max:1024'],
                'bio' => ['nullable', 'string'],
                'timezone' => ['nullable', 'string'],
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
                'show_email' => $data['show_email'],
                'bio' => strip_tags($data['bio']),
                'timezone' => $data['timezone']
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

            return view($this->findView('result'), [
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
        return view($this->findView('result'), [
            'message' => __('You are logged in!'),
            'redirect' => route('index')
        ]);
    }

    /**
     * Get redirection link.
     * 
     * @param $request Incoming request.
     * @param $user Current user.
     * @return string
     */
    private function getRedirect(Request $request, ?User $user)
    {
        $redirect = route('index');

        if ($request->filled('redirect'))
        {
            switch ($request->redirect)
            {
                case 'user.index':
                    $redirect = route($request->redirect);
                break;
                case 'user.show':
                    $redirect = route($request->redirect, ['user' => $user->id]);
                break;
            }
        }

        return $redirect;
    }
}
