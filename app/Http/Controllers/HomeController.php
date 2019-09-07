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

        return view('home', [
            'categories' => $categories,
            'last_category_order' => $last_category_order
        ]);
    }
}
