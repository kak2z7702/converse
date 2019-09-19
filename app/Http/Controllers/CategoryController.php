<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Show the category page.
     *
     * @param $category_slug Category slug.
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show($category_slug)
    {
        $category = Category::where('slug', $category_slug)->with(['topics' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->firstOrFail();

        return view('category.show', ['category' => $category]);
    }

    /**
     * Show the category form to create a category.
     */
    public function create(Request $request)
    {
        $this->authorize('create', 'App\Category');

        if ($request->isMethod('get'))
        {
            return view('category.form', [
                'redirect' => route('index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'title' => 'required|string|max:255'
            ]);

            $category = new Category($data);
            $category->slug = $this->findFreeSlug(Str::slug($data['title'], '-'));
            $category->order = Category::max('order') + 1;
            $category->user_id = auth()->user()->id;

            $category->save();

            return view('result', [
                'message' => __('Category was created successfully.'),
                'redirect' => route('index')
            ]);
        }
    }

    /**
     * Show the category form to update a category.
     * 
     * @param $category Category id.
     */
    public function update(Request $request, $category)
    {
        $category = Category::lockForUpdate()->findOrFail($category);

        $this->authorize('update', $category);

        if ($request->isMethod('get'))
        {
            return view('category.form', [
                'category' => $category,
                'redirect' => $this->getRedirect($request, $category)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255'
            ]);

            if ($validator->fails())
            {
                return redirect()
                    ->route('category.update', [
                            'category' => $category->id,
                            'redirect' => $request->filled('redirect') ? $request->redirect : 'index'
                        ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $old_title = $category->title;

            $category->fill($data);

            if ($data['title'] != $old_title) $category->slug = $this->findFreeSlug(Str::slug($data['title'], '-'), $category->id);

            $category->save();

            return view('result', [
                'message' => __('Category was updated successfully.'),
                'redirect' => $this->getRedirect($request, $category)
            ]);
        }
    }

    /**
     * Delete a category.
     * 
     * @param $category Category id.
     */
    public function delete(Request $request, $category)
    {
        $category = Category::with('topics')->findOrFail($category);

        $this->authorize('delete', $category);

        foreach ($category->topics as $topic)
        {
            $permission = \App\Permission::where('slug', 'topic_management_' . $topic->hash)->first();

            if ($permission) $permission->delete();

            if ($topic->photo && Storage::disk('public')->exists($topic->photo)) Storage::disk('public')->delete($topic->photo);
        }

        $category->delete();

        return view('result', [
            'message' => __('Category was deleted successfully.'),
            'redirect' => route('index')
        ]);
    }

    /**
     * Move a category.
     * 
     * @param $request Incoming request.
     * @param $category Category id.
     * @param $dir Move direction.
     */
    public function move(Request $request, $category, $dir)
    {
        $category = Category::findOrFail($category);

        $this->authorize('move', $category);

        $last_category_order = Category::max('order');

        if ($dir == "up" && $category->order > 1) 
        {
            $prev_category = Category::where('order', '<', $category->order)->first();

            if ($prev_category)
            {
                $prev_category_order = $prev_category->order;

                $prev_category->order = $category->order;

                $prev_category->save();

                $category->order = $prev_category_order;

                $category->save();
            }
        }
        else if ($dir == "down" && $category->order < $last_category_order)
        {
            $next_category = Category::where('order', '>', $category->order)->first();

            if ($next_category)
            {
                $next_category_order = $next_category->order;

                $next_category->order = $category->order;

                $next_category->save();

                $category->order = $next_category_order;

                $category->save();
            }
        }

        return view('result', [
            'message' => __('Category was moved successfully.'),
            'redirect' => route('index')
        ]);
    }

    /**
     * Find a free slug.
     * 
     * @param $slug Slug string.
     * @param $category_id Excluded category id.
     * @return string
     */
    private function findFreeSlug($slug, $category_id = null)
    {
        $counter = 0;

        do 
        {
            if (!$category_id)
                $category = Category::where('slug', $slug)->first();
            else
                $category = Category::where('slug', $slug)->where('id', '!=', $category_id)->first();

            if ($category) $slug .= '-' . ++$counter;
        } while ($category);

        return $slug;
    }

    /**
     * Get redirection link.
     * 
     * @param $request Incoming request.
     * @param $category Current category.
     * @return string
     */
    private function getRedirect(Request $request, Category $category)
    {
        $redirect = route('index');

        if ($request->filled('redirect'))
        {
            switch ($request->redirect)
            {
                case 'category.show':
                    $redirect = route($request->redirect, ['category_slug' => $category->slug]);
                break;
            }
        }

        return $redirect;
    }
}
