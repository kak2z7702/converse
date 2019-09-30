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

        return view('thread.show', [
            'thread' => $thread,
            'comments' => $comments
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

            return view('thread.form', [
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
                    ->route('thread.create', ['redirect' => 'topic.show'])
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

            return view('result', [
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

            return view('thread.form', [
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

            return view('result', [
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
    public function delete(Request $request, $thread)
    {
        $thread = Thread::findOrFail($thread);

        $this->authorize('delete', $thread);

        $thread->comments()->delete();

        $thread->delete();

        return view('result', [
            'message' => __('Thread was deleted successfully.'),
            'redirect' => $this->getRedirect($request, null, $thread)
        ]);
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

        return view('result', [
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

        return view('result', [
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

        return view('result', [
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

        return view('result', [
            'message' => __('Thread was unpinned successfully.'),
            'redirect' => $this->getRedirect($request, null, $thread)
        ]);
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
            }
        }

        return $redirect;
    }
}
