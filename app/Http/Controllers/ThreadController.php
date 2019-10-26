<?php

namespace App\Http\Controllers;

use App\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ThreadController extends Controller
{
    /**
     * Show the thread page.
     *
     * @param $request Incoming request.
     * @param $category_slug Thread's topic category slug.
     * @param $topic_slug Thread's topic slug.
     * @param $thread_slug Thread slug.
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(Request $request, $category_slug, $topic_slug, $thread_slug)
    {
        $category = \App\Category::where('slug', $category_slug)->firstOrFail();
        $topic = $category->topics()->where('slug', $topic_slug)->firstOrFail();
        $thread = $topic->threads()->where('slug', $thread_slug)->firstOrFail();
        $comments = $thread->comments()->orderBy('created_at', 'asc')->paginate(null, ['*'], 'page');

        if (!$request->has('page'))
            $comments = $thread->comments()->orderBy('created_at', 'asc')->paginate(null, ['*'], 'page', $comments->lastPage());

        $is_subscribed = false; 
        $is_favorited = false;

        if (auth()->check())
        {
            $is_subscribed = ($thread->subscriptions()->where('user_id', auth()->user()->id)->first() != null);
            $is_favorited = ($thread->favorites()->where('user_id', auth()->user()->id)->first() != null);
        }

        return view($this->findView('thread.show'), [
            'thread' => $thread,
            'comments' => $comments,
            'is_subscribed' => $is_subscribed,
            'is_favorited' => $is_favorited
        ]);
    }

    /**
     * Show the thread form to create a thread.
     * 
     * @param $request Incoming request.
     */
    public function create(Request $request)
    {
        $this->authorize('create', 'App\Thread');
        
        if ($request->isMethod('get'))
        {
            $categories = \App\Category::with('topics')->orderBy('created_at', 'asc')->get();
            $topic = \App\Topic::findOrFail($request->topic);

            return view($this->findView('thread.form'), [
                'categories' => $categories,
                'topic_id' => $request->topic,
                'redirect' => $this->getRedirect($request, $topic, null)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'topic' => 'required|numeric|exists:topics,id',
            ]);

            if ($validator->fails())
            {
                return redirect()
                    ->route('thread.create', ['topic' => $request->topic, 'redirect' => 'topic.show'])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $topic = \App\Topic::findOrFail($data['topic']);

            $thread = new Thread($data);
            $thread->slug = $this->findFreeSlug(Str::slug($data['title'], '-'), $topic->category->id, $topic->id);
            $thread->user_id = auth()->user()->id;

            $topic->threads()->save($thread);

            $comment = new \App\Comment($data);
            $comment->is_original = true;
            $comment->user_id = auth()->user()->id;
    
            $thread->comments()->save($comment);

            return view($this->findView('result'), [
                'message' => __('Thread was created successfully.'),
                'redirect' => $this->getRedirect($request, null, $thread)
            ]);
        }
    }

    /**
     * Show the thread form to update a thread.
     * 
     * @param $request Incoming request.
     * @param $thread Thread id.
     */
    public function update(Request $request, $thread)
    {
        $thread = Thread::lockForUpdate()->findOrFail($thread);

        $this->authorize('update', $thread);

        if ($request->isMethod('get'))
        {
            $categories = \App\Category::with('topics')->orderBy('created_at', 'asc')->get();

            return view($this->findView('thread.form'), [
                'thread' => $thread,
                'categories' => $categories,
                'redirect' => $this->getRedirect($request, null, $thread)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'topic' => 'required|numeric|exists:topics,id'
            ]);

            if ($validator->fails())
            {
                return redirect()
                    ->route('thread.update', [
                            'thread' => $thread->id,
                            'redirect' => $request->filled('redirect') ? $request->redirect : 'index'
                        ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $topic = \App\Topic::findOrFail($data['topic']);

            $old_title = $thread->title;

            $thread->fill($data);

            if ($data['title'] != $old_title) $thread->slug = $this->findFreeSlug(Str::slug($data['title'], '-'), $topic->category->id, $topic->id, $thread->id);
            
            $topic->threads()->save($thread);

            return view($this->findView('result'), [
                'message' => __('Thread was updated successfully.'),
                'redirect' => $this->getRedirect($request, null, $thread)
            ]);
        }
    }

    /**
     * Delete a thread.
     * 
     * @param $request Incoming request.
     * @param $thread Thread id.
     */
    public function delete(Request $request, $thread = null)
    {
        if ($request->isMethod('get'))
        {
            $thread = Thread::findOrFail($thread);

            $this->authorize('delete', $thread);
    
            $thread->comments()->delete();
    
            $thread->delete();
    
            return view($this->findView('result'), [
                'message' => __('Thread was deleted successfully.'),
                'redirect' => $this->getRedirect($request, null, $thread)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'threads' => 'required|regex:/[0-9]+,?/i'
            ]);

            $threads = Thread::whereIn('id', explode(",", $data['threads']))->get();

            foreach ($threads as $thread)
                $this->authorize('delete', $thread);
    
            foreach ($threads as $thread)
                $thread->comments()->delete();
    
            Thread::whereIn('id', $threads->pluck('id'))->delete();

            return view($this->findView('result'), [
                'message' => __('Threads were deleted successfully.'),
                'redirect' => $this->getRedirect($request, null, $threads[0])
            ]);
        }
    }

    /**
     * Open a thread.
     * 
     * @param $request Incoming request.
     * @param $thread Thread id.
     */
    public function open(Request $request, $thread)
    {
        $thread = Thread::findOrFail($thread);

        $this->authorize('open', $thread);

        $thread->is_open = true;

        $thread->save();

        return view($this->findView('result'), [
            'message' => __('Thread was opened successfully.'),
            'redirect' => $this->getRedirect($request, null, $thread)
        ]);
    }

    /**
     * Close a thread.
     * 
     * @param $request Incoming request.
     * @param $thread Thread id.
     */
    public function close(Request $request, $thread)
    {
        $thread = Thread::findOrFail($thread);

        $this->authorize('open', $thread);

        $thread->is_open = false;

        $thread->save();

        return view($this->findView('result'), [
            'message' => __('Thread was closed successfully.'),
            'redirect' => $this->getRedirect($request, null, $thread)
        ]);
    }

    /**
     * Pin a thread.
     * 
     * @param $request Incoming request.
     * @param $thread Thread id.
     */
    public function pin(Request $request, $thread)
    {
        $thread = Thread::findOrFail($thread);

        $this->authorize('pin', $thread);

        $thread->is_pinned = true;

        $thread->save();

        return view($this->findView('result'), [
            'message' => __('Thread was pinned successfully.'),
            'redirect' => $this->getRedirect($request, null, $thread)
        ]);
    }

    /**
     * Unpin a thread.
     * 
     * @param $request Incoming request.
     * @param $thread Thread id.
     */
    public function unpin(Request $request, $thread)
    {
        $thread = Thread::findOrFail($thread);

        $this->authorize('pin', $thread);

        $thread->is_pinned = false;

        $thread->save();

        return view($this->findView('result'), [
            'message' => __('Thread was unpinned successfully.'),
            'redirect' => $this->getRedirect($request, null, $thread)
        ]);
    }

    /**
     * Subscribe to a thread.
     * 
     * @param $request Incoming request.
     * @param $thread Thread id.
     */
    public function subscribe(Request $request, $thread)
    {
        $thread = Thread::findOrFail($thread);

        $this->authorize('subscribe', $thread);

        $is_subscribed = $thread->subscriptions()->where('user_id', auth()->user()->id)->first();

        if ($is_subscribed) return redirect()->back();

        $thread->subscriptions()->save(new \App\Subscription(['user_id' => auth()->user()->id]));

        return view($this->findView('result'), [
            'message' => __('Thread was subscribed to successfully.'),
            'redirect' => $this->getRedirect($request, null, $thread)
        ]);
    }

    /**
     * Unsubscribe to a thread.
     * 
     * @param $request Incoming request.
     * @param $thread Thread id.
     */
    public function unsubscribe(Request $request, $thread = null)
    {
        if ($request->isMethod('get'))
        {
            $thread = Thread::findOrFail($thread);

            $this->authorize('subscribe', $thread);

            $is_subscribed = $thread->subscriptions()->where('user_id', auth()->user()->id)->first();

            if (!$is_subscribed) return redirect()->back();

            $thread->subscriptions()->where('user_id', auth()->user()->id)->delete();

            return view($this->findView('result'), [
                'message' => __('Thread was unsubscribed from successfully.'),
                'redirect' => $this->getRedirect($request, null, $thread)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'threads' => 'required|regex:/[0-9]+,?/i'
            ]);

            $threads = Thread::whereIn('id', explode(",", $data['threads']))->get();

            foreach ($threads as $thread)
                $this->authorize('subscribe', $thread);

            \App\Subscription::whereIn('thread_id', $threads->pluck('id'))->where('user_id', auth()->user()->id)->delete();
    
            return view($this->findView('result'), [
                'message' => __('Threads were unsubscribed from successfully.'),
                'redirect' => $this->getRedirect($request, null, $threads[0])
            ]);
        }
    }

    /**
     * Favorite a thread.
     * 
     * @param $request Incoming request.
     * @param $thread Thread id.
     */
    public function favorite(Request $request, $thread)
    {
        $thread = Thread::findOrFail($thread);

        $this->authorize('favorite', $thread);

        $is_favorited = $thread->favorites()->where('user_id', auth()->user()->id)->first();

        if ($is_favorited) return redirect()->back();

        $thread->favorites()->save(new \App\Favorite(['user_id' => auth()->user()->id]));

        return view($this->findView('result'), [
            'message' => __('Thread was favorited successfully.'),
            'redirect' => $this->getRedirect($request, null, $thread)
        ]);
    }

    /**
     * Unfavorite a thread.
     * 
     * @param $request Incoming request.
     * @param $thread Thread id.
     */
    public function unfavorite(Request $request, $thread = null)
    {
        if ($request->isMethod('get'))
        {
            $thread = Thread::findOrFail($thread);

            $this->authorize('favorite', $thread);

            $is_favorited = $thread->favorites()->where('user_id', auth()->user()->id)->first();

            if (!$is_favorited) return redirect()->back();

            $thread->favorites()->where('user_id', auth()->user()->id)->delete();

            return view($this->findView('result'), [
                'message' => __('Thread was unfavorited successfully.'),
                'redirect' => $this->getRedirect($request, null, $thread)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'threads' => 'required|regex:/[0-9]+,?/i'
            ]);

            $threads = Thread::whereIn('id', explode(",", $data['threads']))->get();

            foreach ($threads as $thread)
                $this->authorize('favorite', $thread);

            \App\Favorite::whereIn('thread_id', $threads->pluck('id'))->where('user_id', auth()->user()->id)->delete();
    
            return view($this->findView('result'), [
                'message' => __('Threads were unfavorited successfully.'),
                'redirect' => $this->getRedirect($request, null, $threads[0])
            ]);
        }
    }

    /**
     * Find a free slug.
     * 
     * @param $slug Slug string.
     * @param $category_id Current category id.
     * @param $topic_id Current topic id.
     * @param $thread_id Excluded thread id.
     * @return string
     */
    private function findFreeSlug($slug, $category_id, $topic_id, $thread_id = null)
    {
        $counter = 0;

        do 
        {
            if (!$thread_id)
            {
                $thread = Thread::where('slug', $slug)->whereHas('topic', function ($query) use ($category_id, $topic_id) {
                    $query->where('id', $topic_id)->whereHas('category', function ($query) use ($category_id) {
                        $query->where('id', $category_id);
                    });
                })->first();
            }
            else
            {
                $thread = Thread::where('id', '!=', $thread_id)->where('slug', $slug)->whereHas('topic', function ($query) use ($category_id, $topic_id) {
                    $query->where('id', $topic_id)->whereHas('category', function ($query) use ($category_id) {
                        $query->where('id', $category_id);
                    });
                })->first();
            }

            if ($thread) $slug .= '-' . ++$counter;
        } while ($thread);

        return $slug;
    }

    /**
     * Get redirection link.
     * 
     * @param $request Incoming request.
     * @param $topic Current topic.
     * @param $thread Current thread.
     * @return string
     */
    private function getRedirect(Request $request, ?\App\Topic $topic, ?Thread $thread)
    {
        $redirect = route('index');

        if ($request->filled('redirect'))
        {
            switch ($request->redirect)
            {
                case 'topic.show':
                    if (isset($topic))
                    {
                        $redirect = route($request->redirect, [
                            'category_slug' => $topic->category->slug,
                            'topic_slug' => $topic->slug,
                        ]);
                    }
                    else if (isset($thread))
                    {
                        $redirect = route($request->redirect, [
                            'category_slug' => $thread->topic->category->slug,
                            'topic_slug' => $thread->topic->slug,
                        ]);
                    }
                break;
                case 'thread.show':
                    $redirect = route($request->redirect, [
                        'category_slug' => $thread->topic->category->slug,
                        'topic_slug' => $thread->topic->slug,
                        'thread_slug' => $thread->slug
                    ]);
                break;
                case 'user.subscriptions':
                    $redirect = route($request->redirect, ['user' => auth()->user()->id]);
                break;
                case 'user.favorites':
                    $redirect = route($request->redirect, ['user' => auth()->user()->id]);
                break;
            }
        }

        return $redirect;
    }
}
