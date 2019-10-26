<?php

namespace App\Http\Controllers;

use App\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * Show the pages management page.
     *
     * @param $request Incoming request.
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', 'App\Page');

        $pages = null;

        if (!$request->filled('q'))
        {
            $pages = Page::orderBy('created_at', 'asc')->paginate();
        }
        else
        {
            $data = $request->validate(['q' => 'string|max:256']);

            $pages = Page::where('title', 'like', $data['q'] . '%')->orderBy('created_at', 'asc')->paginate();

            $pages->appends(['q' => request()->q]);
        }  

        return view($this->findView('page.index'), ['pages' => $pages]);
    }

    /**
     * Show the custom page.
     *
     * @param $request Incoming request.
     * @param $slug Page slug.
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(Request $request, $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $comments = $page->comments()->orderBy('created_at', 'asc')->paginate(null, ['*'], 'comments_page');

        if (!$request->has('comments_page'))
            $comments = $page->comments()->orderBy('created_at', 'asc')->paginate(null, ['*'], 'comments_page', $comments->lastPage());

        return view($this->findView('page.show'), [
            'page' => $page,
            'comments' => $comments
        ]);
    }

    /**
     * Show the page form to create a page.
     * 
     * @param $request Incoming request.
     */
    public function create(Request $request)
    {
        $this->authorize('create', 'App\Page');

        if ($request->isMethod('get'))
        {
            return view($this->findView('page.form'), [
                'redirect' => route('page.index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'can_have_comments' => 'required|in:on,off'
            ]);

            $page = new Page($data);
            $page->slug = $this->findFreeSlug(Str::slug($data['title'], '-'));
            $page->user_id = auth()->user()->id;

            $page->save();

            return view($this->findView('result'), [
                'message' => __('Page was created successfully.'),
                'redirect' => route('page.index')
            ]);
        }
    }

    /**
     * Show the page form to update a page.
     * 
     * @param $request Incoming request.
     * @param $page Page id.
     */
    public function update(Request $request, $page)
    {
        $page = Page::lockForUpdate()->findOrFail($page);

        $this->authorize('update', $page);

        if ($request->isMethod('get'))
        {
            return view($this->findView('page.form'), [
                'page' => $page,
                'redirect' => $this->getRedirect($request, $page)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'can_have_comments' => 'required|in:on,off'
            ]);

            if ($validator->fails())
            {
                return redirect()
                    ->route('page.update', [
                            'page' => $page->id,
                            'redirect' => $request->filled('redirect') ? $request->redirect : 'index'
                        ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $old_title = $page->title;

            $page->fill($data);

            if ($data['title'] != $old_title) $page->slug = $this->findFreeSlug(Str::slug($data['title'], '-'), $page->id);

            $page->save();

            return view($this->findView('result'), [
                'message' => __('Page was updated successfully.'),
                'redirect' => $this->getRedirect($request, $page)
            ]);
        }
    }

    /**
     * Delete a page.
     * 
     * @param $request Incoming request.
     * @param $page Page id.
     */
    public function delete(Request $request, $page = null)
    {
        if ($request->isMethod('get'))
        {
            $page = Page::findOrFail($page);

            $this->authorize('delete', $page);
    
            $page->comments()->delete();
    
            $page->delete();
    
            return view($this->findView('result'), [
                'message' => __('Page was deleted successfully.'),
                'redirect' => $this->getRedirect($request, $page)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'pages' => 'required|regex:/[0-9]+,?/i'
            ]);

            $pages = Page::whereIn('id', explode(",", $data['pages']))->get();

            foreach ($pages as $page)
                $this->authorize('delete', $page);

            Page::whereIn('id', $pages->pluck('id'))->delete();

            return view($this->findView('result'), [
                'message' => __('Pages were deleted successfully.'),
                'redirect' => route('page.index')
            ]);
        }
    }

    /**
     * Find a free slug.
     * 
     * @param $slug Slug string.
     * @param $page_id Excluded page id.
     * @return string
     */
    private function findFreeSlug($slug, $page_id = null)
    {
        $counter = 0;

        do 
        {
            if (!$page_id)
                $page = Page::where('slug', $slug)->first();
            else
                $page = Page::where('slug', $slug)->where('id', '!=', $page_id)->first();

            if ($page) $slug .= '-' . ++$counter;
        } while ($page);

        return $slug;
    }

    /**
     * Get redirection link.
     * 
     * @param $request Incoming request.
     * @param $page Current page.
     * @return string
     */
    private function getRedirect(Request $request, Page $page)
    {
        $redirect = route('index');

        if ($request->filled('redirect'))
        {
            switch ($request->redirect)
            {
                case 'page.index':
                    $redirect = route($request->redirect);
                break;
                case 'page.show':
                    $redirect = route($request->redirect, ['slug' => $page->slug]);
                break;
            }
        }

        return $redirect;
    }
}
