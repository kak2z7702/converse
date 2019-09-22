<?php

namespace App\Http\Controllers;

use App\User;
use App\Role;
use App\Permission;
use App\Page;
use App\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class InstallController extends Controller
{
    /**
     * Show the application install page.
     *
     * @param $request Incoming request.
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function install(Request $request)
    {
        // abort if installation is sealed
        if (Storage::exists('installed')) return abort(404);

        if ($request->isMethod('get'))
        {
            return view('install');
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'community_name' => 'required|string|max:64',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed'
            ]);

            // admin user
            $user = new User([
                'name' => 'Administration',
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->is_admin = true;

            $user->save();

            // topic management role
            $topic_role = new Role(['title' => 'Topic Management']);
            $topic_role->is_protected = true;

            $topic_role->save();
            
            $topic_role->permissions()->saveMany([
                new Permission(['title' => 'Topic Creation', 'slug' => 'topic_create']),
                new Permission(['title' => 'Topic Updating', 'slug' => 'topic_update']),
                new Permission(['title' => 'Topic Deletion', 'slug' => 'topic_delete']),
                new Permission(['title' => 'Topic Movement', 'slug' => 'topic_move'])
            ]);

            // thread management role
            $thread_role = new Role(['title' => 'Thread Management']);
            $thread_role->is_protected = true;

            $thread_role->save();
            
            $thread_role->permissions()->saveMany([
                new Permission(['title' => 'Thread Creation', 'slug' => 'thread_create']),
                new Permission(['title' => 'Thread Updating', 'slug' => 'thread_update']),
                new Permission(['title' => 'Thread Deletion', 'slug' => 'thread_delete'])
            ]);

            // comment management role
            $comment_role = new Role(['title' => 'Comment Management']);
            $comment_role->is_protected = true;

            $comment_role->save();
            
            $comment_role->permissions()->saveMany([
                new Permission(['title' => 'Comment Creation', 'slug' => 'comment_create']),
                new Permission(['title' => 'Comment Updating', 'slug' => 'comment_update']),
                new Permission(['title' => 'Comment Deletion', 'slug' => 'comment_delete'])
            ]);

            // message management role
            $message_role = new Role(['title' => 'Message Management']);
            $message_role->is_protected = true;

            $message_role->save();
            
            $message_role->permissions()->saveMany([
                new Permission(['title' => 'Message Creation', 'slug' => 'message_create']),
                new Permission(['title' => 'Message Updating', 'slug' => 'message_update']),
                new Permission(['title' => 'Message Deletion', 'slug' => 'message_delete'])
            ]);

            // about page
            $about_page = new Page(['title' => 'About', 'slug' => 'about', 'content' => 'About page.']);
            $about_page->user_id = $user->id;

            $about_page->save();

            // contact page
            $contact_page = new Page(['title' => 'Contact', 'slug' => 'contact', 'content' => 'Contact page.']);
            $contact_page->user_id = $user->id;

            $contact_page->save();

            // policy page
            $policy_page = new Page(['title' => 'Policy', 'slug' => 'policy', 'content' => 'Policy page.']);
            $policy_page->user_id = $user->id;

            $policy_page->save();

            // home menu
            $home_menu = new Menu(['title' => 'Home', 'url' => '/']);
            $home_menu->order = 1;
            $home_menu->user_id = $user->id;

            $home_menu->save();

            // about menu
            $about_menu = new Menu(['title' => 'About', 'url' => '/about']);
            $about_menu->order = 2;
            $about_menu->user_id = $user->id;

            $about_menu->save();

            // contact menu
            $contact_menu = new Menu(['title' => 'Contact', 'url' => '/contact']);
            $contact_menu->order = 3;
            $contact_menu->user_id = $user->id;

            $contact_menu->save();

            // policy menu
            $policy_menu = new Menu(['title' => 'Policy', 'url' => '/policy']);
            $policy_menu->order = 4;
            $policy_menu->user_id = $user->id;

            $policy_menu->save();

            // community options
            $options = [
                'community' => [
                    'name' => $data['community_name'],
                    'birthday' => now()->format('Y-m-d H:i')->toDateTimeString()
                ]
            ];

            // make options file
            Storage::put('options.json', json_encode($options));

            // installation seal proof
            Storage::put('installed', null);

            // login admin user
            auth()->guard()->login($user);

            return view('result', [
                'message' => __('Installation was successful.'),
                'redirect' => route('index')
            ]);
        }
    }

    /**
     * Show the application options page.
     *
     * @param $request Incoming request.
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function options(Request $request)
    {   
        if ($request->isMethod('get'))
        {
            return view('options', [
                'redirect' => route('index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'community_name' => 'required|string|max:64'
            ]);

            // community options
            $options = null;

            // read options file
            if (Storage::exists('options.json'))
            {
                // decode from json
                $options = json_decode(Storage::get('options.json'));
            }

            // community options
            $options = [
                'community' => [
                    'name' => $data['community_name'],
                    'birthday' => ($options) ? $options->community->birthday : now()->format('Y-m-d H:i')->toDateTimeString()
                ]
            ];

            // make options file
            Storage::put('options.json', json_encode($options));

            return view('result', [
                'message' => __('Options updated successfully.'),
                'redirect' => route('index')
            ]);
        }
    }
}
