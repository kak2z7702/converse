<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $categories = Category::orderBy('order', 'asc')->with(['topics' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->get();
        
        $last_category_order = Category::max('order');

        // latest user
        $latest_user = \App\User::orderBy('created_at', 'desc')->first();

        // latest thread
        $latest_thread = \App\Thread::orderBy('created_at', 'desc')->first();

        // latest comment
        $latest_comment = \App\Comment::orderBy('created_at', 'desc')->first();

        return view('home', [
            'categories' => $categories,
            'last_category_order' => $last_category_order,
            'activity' => [
                'active_thread' => ($latest_comment) ? $latest_comment->thread : null,
                'active_user' => ($latest_comment) ? $latest_comment->user : null,
                'latest_thread' => $latest_thread,
                'latest_user' => $latest_user,
                'members_count' => \App\User::count(),
                'topics_count' => \App\Topic::count(),
                'threads_count' => \App\Thread::count(),
            ]
        ]);
    }

    /**
     * Accept cookie consent.
     */
    public function consent()
    {
        return response('Cookie consent accepted.')->cookie('converse_cookie_consent', 'true', now()->addYear()->diffInMinutes());
    }
}
