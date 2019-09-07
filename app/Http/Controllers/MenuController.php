<?php

namespace App\Http\Controllers;

use App\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Show the menus management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $this->authorize('viewAny', 'App\Menu');

        $menus = Menu::orderBy('order', 'asc')->paginate();
        $last_menu_order = Menu::max('order');

        return view('menu.index', [
            'menus' => $menus,
            'last_menu_order' => $last_menu_order
        ]);
    }

    /**
     * Show the menu form to create a menu.
     * 
     * @param $request Incoming request.
     */
    public function create(Request $request)
    {
        $this->authorize('create', 'App\Menu');

        if ($request->isMethod('get'))
        {
            return view('menu.form', [
                'redirect' => route('menu.index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'url' => 'required|string|max:255',
            ]);

            $menu = new Menu($data);
            $menu->order = Menu::max('order') + 1;
            $menu->user_id = auth()->user()->id;

            $menu->save();

            return view('result', [
                'message' => __('Menu was created successfully.'),
                'redirect' => route('menu.index')
            ]);
        }
    }

    /**
     * Show the menu form to update a menu.
     * 
     * @param $request Incoming request.
     * @param $menu Menu id.
     */
    public function update(Request $request, $menu)
    {
        $menu = Menu::lockForUpdate()->findOrFail($menu);

        $this->authorize('update', $menu);

        if ($request->isMethod('get'))
        {
            return view('menu.form', [
                'menu' => $menu,
                'redirect' => route('menu.index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'url' => 'required|string|max:255',
            ]);

            $menu->fill($data)->save();

            return view('result', [
                'message' => __('Menu was updated successfully.'),
                'redirect' => route('menu.index')
            ]);
        }
    }

    /**
     * Delete a menu.
     * 
     * @param $request Incoming request.
     * @param $menu Menu id.
     */
    public function delete(Request $request, $menu)
    {
        $menu = Menu::findOrFail($menu);

        $this->authorize('delete', $menu);

        $menu->delete();

        return view('result', [
            'message' => __('Menu was deleted successfully.'),
            'redirect' => route('menu.index')
        ]);
    }

    /**
     * Move a menu.
     * 
     * @param $request Incoming request.
     * @param $menu Menu id.
     * @param $dir Move direction.
     */
    public function move(Request $request, $menu, $dir)
    {
        $menu = Menu::findOrFail($menu);

        $this->authorize('move', $menu);

        $last_menu_order = Menu::max('order');

        if ($dir == "up" && $menu->order > 1) 
        {
            $prev_menu = Menu::where('order', '<', $menu->order)->first();

            if ($prev_menu)
            {
                $prev_menu_order = $prev_menu->order;

                $prev_menu->order = $menu->order;

                $prev_menu->save();

                $menu->order = $prev_menu_order;

                $menu->save();
            }
        }
        else if ($dir == "down" && $menu->order < $last_menu_order)
        {
            $next_menu = Menu::where('order', '>', $menu->order)->first();

            if ($next_menu)
            {
                $next_menu_order = $next_menu->order;

                $next_menu->order = $menu->order;

                $next_menu->save();

                $menu->order = $next_menu_order;

                $menu->save();
            }
        }

        return view('result', [
            'message' => __('Menu was moved successfully.'),
            'redirect' => route('menu.index')
        ]);
    }
}
