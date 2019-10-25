<?php

namespace App\Http\Controllers;

use App\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TopicController extends Controller
{
    /**
     * Show the topic page.
     *
     * @param $request Incoming request.
     * @param $category_slug Topic's category slug.
     * @param $topic_slug Topic slug.
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(Request $request, $category_slug, $topic_slug)
    {
        $category = \App\Category::where('slug', $category_slug)->firstOrFail();
        $topic = $category->topics()->where('slug', $topic_slug)->firstOrFail();

        $threads = null;

        if (!$request->filled('q'))
        {
            $threads = $topic->threads()
                ->orderBy('is_pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate();
        }
        else
        {
            $data = $request->validate(['q' => 'string|max:256']);

            $threads = $topic->threads()
                ->where('title', 'like', $data['q'] . '%')
                ->orderBy('is_pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate();

            $threads->appends(['q' => request()->q]);
        }

        return view($this->findView('topic.show'), [
            'topic' => $topic,
            'threads' => $threads
        ]);
    }

    /**
     * Show the topic form to create a topic.
     * 
     * @param $request Incoming request.
     */
    public function create(Request $request)
    {
        $this->authorize('create', 'App\Topic');

        if ($request->isMethod('get'))
        {
            $categories = \App\Category::orderBy('created_at', 'asc')->get();
            $category = \App\Category::findOrFail($request->category);

            return view($this->findView('topic.form'), [
                'categories' => $categories,
                'category_id' => $request->category,
                'redirect' => $this->getRedirect($request, $category, null)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'photo' => 'nullable|image|max:1024',
                'category' => 'required|numeric|exists:categories,id',
            ]);

            if ($validator->fails())
            {
                return redirect()
                    ->route('topic.create', ['redirect' => $request->filled('redirect') ? $request->redirect : 'index'])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $category = \App\Category::findOrFail($data['category']);

            $topic = new Topic($data);
            $topic->slug = $this->findFreeSlug(Str::slug($data['title'], '-'), $category->id);
            $topic->order = Topic::max('order') + 1;
            $topic->hash = md5(now()->toDateTimeString());
            $topic->user_id = auth()->user()->id;

            if ($request->has('photo')) $topic->photo = $request->photo->store('images/topics', 'public');

            $category->topics()->save($topic);

            $permission = \App\Permission::create([
                'title' => $topic->title . __(' Topic Management'),
                'slug' => 'topic_management_' . $topic->hash,
            ]);

            $permission->save();

            return view($this->findView('result'), [
                'message' => __('Topic was created successfully.'),
                'redirect' => $this->getRedirect($request, null, $topic)
            ]);
        }
    }

    /**
     * Show the topic form to update a topic.
     * 
     * @param $request Incoming request.
     * @param $topic Topic id.
     */
    public function update(Request $request, $topic)
    {
        $topic = Topic::lockForUpdate()->findOrFail($topic);

        $this->authorize('update', $topic);

        if ($request->isMethod('get'))
        {
            $categories = \App\Category::orderBy('created_at', 'asc')->get();

            return view($this->findView('topic.form'), [
                'topic' => $topic,
                'categories' => $categories,
                'redirect' => $this->getRedirect($request, null, $topic)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required|max:255',
                'description' => 'nullable|max:255',
                'photo' => 'nullable|image|max:1024',
                'category' => 'required|numeric|exists:categories,id',
            ]);

            if ($validator->fails())
            {
                return redirect()
                    ->route('topic.update', [
                            'topic' => $topic->id,
                            'redirect' => $request->filled('redirect') ? $request->redirect : 'index'
                        ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $category = \App\Category::findOrFail($data['category']);

            $old_title = $topic->title;

            $topic->fill($data);

            if ($data['title'] != $old_title) $topic->slug = $this->findFreeSlug(Str::slug($data['title'], '-'), $category->id, $topic->id);

            if ($request->has('no_photo') && $request->no_photo == 'on')
            {
                if (Storage::disk('public')->exists($topic->photo)) Storage::disk('public')->delete($topic->photo);

                $topic->photo = null;
            }
            else if ($request->has('photo'))
            {
                if ($topic->photo && Storage::disk('public')->exists($topic->photo)) Storage::disk('public')->delete($topic->photo);

                $topic->photo = $request->photo->store('images/topics', 'public');
            }
            
            $category->topics()->save($topic);

            if ($data['title'] !== $old_title)
            {
                $permission = \App\Permission::where('slug', 'topic_management_' . $topic->hash)->first();

                if ($permission)
                {
                    $permission->title = $topic->title . __(' Topic Management');

                    $permission->save();
                }
            }
            
            return view($this->findView('result'), [
                'message' => __('Topic was updated successfully.'),
                'redirect' => $this->getRedirect($request, null, $topic)
            ]);
        }
    }

    /**
     * Delete a topic.
     * 
     * @param $request Incoming request.
     * @param $topic Topic id.
     */
    public function delete(Request $request, $topic)
    {
        $topic = Topic::with('threads')->findOrFail($topic);

        $this->authorize('delete', $topic);

        foreach ($topic->threads as $thread)
            $thread->comments()->delete();

        \App\Permission::where('slug', 'topic_management_' . $topic->hash)->delete();

        if ($topic->photo && Storage::disk('public')->exists($topic->photo)) Storage::disk('public')->delete($topic->photo);

        $topic->delete();

        return view($this->findView('result'), [
            'message' => __('Topic was deleted successfully.'),
            'redirect' => $this->getRedirect($request, null, $topic)
        ]);
    }

    /**
     * Move a topic.
     * 
     * @param $request Incoming request.
     * @param $topic Topic id.
     * @param $dir Move direction.
     */
    public function move(Request $request, $topic, $dir)
    {
        $topic = Topic::findOrFail($topic);

        $last_topic_order = Topic::where('category_id', $topic->category_id)->max('order');

        if ($dir == "up" && $topic->order > 1) 
        {
            $prev_topic = Topic::where('category_id', $topic->category_id)->where('order', '<', $topic->order)->first();

            if ($prev_topic)
            {
                $prev_topic_order = $prev_topic->order;
                
                $prev_topic->order = $topic->order;

                $prev_topic->save();

                $topic->order = $prev_topic_order;

                $topic->save();
            }
        }
        else if ($dir == "down" && $topic->order < $last_topic_order)
        {
            $next_topic = Topic::where('category_id', $topic->category_id)->where('order', '>', $topic->order)->first();

            if ($next_topic)
            {
                $next_topic_order = $next_topic->order;

                $next_topic->order = $topic->order;

                $next_topic->save();

                $topic->order = $next_topic_order;

                $topic->save();
            }
        }

        return view($this->findView('result'), [
            'message' => __('Topic was moved successfully.'),
            'redirect' =>  $this->getRedirect($request, null, $topic)
        ]);
    }

    /**
     * Find a free slug.
     * 
     * @param $slug Slug string.
     * @param $category_id Current category id.
     * @param $topic_id Excluded topic id.
     * @return string
     */
    private function findFreeSlug($slug, $category_id, $topic_id = null)
    {
        $counter = 0;

        do 
        {
            if (!$topic_id)
            {
                $topic = Topic::where('slug', $slug)->whereHas('category', function ($query) use ($category_id) {
                    $query->where('id', $category_id);
                })->first();
            }
            else
            {
                $topic = Topic::where('id', '!=', $topic_id)->where('slug', $slug)->whereHas('category', function ($query) use ($category_id) {
                    $query->where('id', $category_id);
                })->first();
            }

            if ($topic) $slug .= '-' . ++$counter;
        } while ($topic);

        return $slug;
    }

    /**
     * Get redirection link.
     * 
     * @param $request Incoming request.
     * @param $category Current category.
     * @param $topic Current topic.
     * @return string
     */
    private function getRedirect(Request $request, ?\App\Category $category, ?Topic $topic)
    {
        $redirect = route('index');

        if ($request->filled('redirect'))
        {
            switch ($request->redirect)
            {
                case 'category.show':
                    if (isset($category))
                    {
                        $redirect = route($request->redirect, [
                            'category_slug' => $category->slug
                        ]);
                    }
                    else if (isset($topic))
                    {
                        $redirect = route($request->redirect, [
                            'category_slug' => $topic->category->slug
                        ]);
                    }
                break;
                case 'topic.show':
                    $redirect = route($request->redirect, [
                        'category_slug' => $topic->category->slug,
                        'topic_slug' => $topic->slug
                    ]);
                break;
            }
        }

        return $redirect;
    }
}
