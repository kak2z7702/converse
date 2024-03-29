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
     * Show the application welcome page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function welcome()
    {   
        return view($this->findView('welcome'));
    }

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
            return view($this->findView('install'));
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'name' => 'required|string|max:64',
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
            $topic_role->slug = 'topic_management';
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
            $thread_role->slug = 'thread_management';
            $thread_role->is_protected = true;

            $thread_role->save();
            
            $thread_role->permissions()->saveMany([
                new Permission(['title' => 'Thread Creation', 'slug' => 'thread_create']),
                new Permission(['title' => 'Thread Updating', 'slug' => 'thread_update']),
                new Permission(['title' => 'Thread Deletion', 'slug' => 'thread_delete'])
            ]);

            // comment management role
            $comment_role = new Role(['title' => 'Comment Management']);
            $comment_role->slug = 'comment_management';
            $comment_role->is_protected = true;

            $comment_role->save();
            
            $comment_role->permissions()->saveMany([
                new Permission(['title' => 'Comment Creation', 'slug' => 'comment_create']),
                new Permission(['title' => 'Comment Updating', 'slug' => 'comment_update']),
                new Permission(['title' => 'Comment Deletion', 'slug' => 'comment_delete'])
            ]);

            // message management role
            $message_role = new Role(['title' => 'Message Management']);
            $message_role->slug = 'message_management';
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
                'app.name' => $data['name'],
                'app.birthday' => now()->format('Y-m-d H:i'),
                'app.theme' => 'light',
                'app.background.color' => null,
                'app.background.image' => null,
                'app.display_cookie_consent' => false
            ];

            // make options file
            Storage::put('options.json', json_encode($options));

            // installation seal proof
            Storage::put('installed', null);

            // login admin user
            auth()->guard()->login($user);

            return view($this->findView('result'), [
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
            // community themes
            $themes = array();

            // views path
            $views_path = resource_path('views');

            // views directory file infos
            $file_infos = new \DirectoryIterator($views_path);
            
            // get theme directories
            foreach ($file_infos as $file_info)
            {
                if ($file_info->isDir() && !$file_info->isDot() && $file_info->getFilename() != 'layouts')
                {
                    $theme_folder = $file_info->getFilename();
                    $theme_xml = $views_path . DIRECTORY_SEPARATOR . $theme_folder . DIRECTORY_SEPARATOR . 'theme.xml';

                    if (file_exists($theme_xml))
                    {
                        $theme_doc = simplexml_load_file($theme_xml);
                        
                        $theme = json_decode(json_encode($theme_doc), false);
                        $theme->id = $theme_folder;
    
                        $themes[] = $theme;
                    }
                }
            }

            return view($this->findView('options'), [
                'redirect' => route('index'),
                'themes' => $themes
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'name' => 'required|string|max:64',
                'theme' => 'required|string|max:64',
                'background_color' => 'nullable|regex:/^#[0-9a-zA-Z]{6}$/i',
                'background_image' => 'nullable|image|max:1024',
                'display_cookie_consent' => 'required|in:on,off'
            ]);

            // community options
            $options = null;

            // read options file
            if (Storage::exists('options.json'))
            {
                // decode from json
                $options = json_decode(Storage::get('options.json'), true);
            }

            // delete old background image
            if ($request->has('no_background_image') && $request->no_background_image == 'on')
            {
                if (Storage::disk('public')->exists($options['app.background.image'])) Storage::disk('public')->delete($options['app.background.image']);

                $options['app.background.image'] = null;
            }
            else if ($request->has('background_image'))
            {
                if ($options['app.background.image'] && Storage::disk('public')->exists($options['app.background.image'])) Storage::disk('public')->delete($options['app.background.image']);

                $options['app.background.image'] = $request->background_image->store('images', 'public');
            }

            // set community options
            $options = [
                'app.name' => $data['name'],
                'app.birthday' => $options['app.birthday'],
                'app.theme' => $data['theme'],
                'app.background.color' => $data['background_color'],
                'app.background.image' => $options['app.background.image'],
                'app.display_cookie_consent' => ($data['display_cookie_consent'] == 'on')
            ];

            // make options file
            Storage::put('options.json', json_encode($options));

            return view($this->findView('result'), [
                'message' => __('Options updated successfully.'),
                'redirect' => route('index')
            ]);
        }
    }
}
