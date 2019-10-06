<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Events\CommentPosted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Create a comment.
     * 
     * @param $request Incoming request.
     */
    public function create(Request $request)
    {
        $this->authorize('create', 'App\Comment');

        $rules = array(
            'content' => ['required', 'string']
        );

        if ($request->filled('page'))
            $rules['page'] = ['required', 'numeric', 'exists:pages,id'];
        else if ($request->filled('thread'))
            $rules['thread'] = ['required', 'numeric', 'exists:threads,id'];
        else
            return redirect()->back();

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
        {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->valid();

        $model = null;

        if ($request->has('page'))
        {
            $model = \App\Page::findOrFail($data['page']);

            $this->authorize('comment', $model);

            $data['content'] = strip_tags($data['content']);
        }
        else if ($request->has('thread'))
        {
            $model = \App\Thread::findOrFail($data['thread']);

            $this->authorize('comment', $model);
        }

        $comment = new Comment($data);
        $comment->user_id = auth()->user()->id;

        $model->comments()->save($comment);

        event(new CommentPosted($comment));

        return view('result', [
            'message' => __('Comment was posted successfully.'),
            'redirect' => $this->getRedirect($request, $comment)
        ]);
    }

    /**
     * Show the comment form to update a comment.
     * 
     * @param $request Incoming request.
     * @param $comment Comment id.
     */
    public function update(Request $request, $comment)
    {
        $comment = Comment::lockForUpdate()->findOrFail($comment);

        $this->authorize('update', $comment);

        if ($request->isMethod('get'))
        {
            return view('comment.form', [
                'comment' => $comment,
                'redirect' => $this->getRedirect($request, $comment)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(), [
                'content' => ['required', 'string']
            ]);

            if ($validator->fails())
            {
                $redirect = null;

                switch ($comment->entity_type)
                {
                    case "App\Page": $redirect = 'page.show'; break;
                    case "App\Thread": $redirect = 'page.thread'; break;
                }

                return redirect()
                    ->route('comment.update', [
                            'comment' => $comment->id,
                            'redirect' => $redirect
                        ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            if ($comment->entity_type == 'App\Page')
            {
                $this->authorize('comment', $comment->page);

                $data['content'] = strip_tags($data['content']);
            }
            else if ($comment->entity_type == 'App\Thread')
            {
                $this->authorize('comment', $comment->thread);
            }

            $comment->fill($data);
            
            $comment->save();

            return view('result', [
                'message' => __('Comment was updated successfully.'),
                'redirect' => $this->getRedirect($request, $comment)
            ]);
        }
    }

    /**
     * Delete a comment.
     * 
     * @param $request Incoming request.
     * @param $comment Comment id.
     */
    public function delete(Request $request, $comment)
    {
        $comment = Comment::findOrFail($comment);

        $this->authorize('delete', $comment);

        if ($comment->entity_type == 'App\Page')
            $this->authorize('comment', $comment->page);
        else if ($comment->entity_type == 'App\Thread')
            $this->authorize('comment', $comment->thread);

        $comment->delete();

        return view('result', [
            'message' => __('Comment was deleted successfully.'),
            'redirect' => $this->getRedirect($request, $comment)
        ]);
    }

    /**
     * Get redirection link.
     * 
     * @param $request Incoming request.
     * @param $comment Current comment.
     * @return string
     */
    private function getRedirect(Request $request, Comment $comment)
    {
        $redirect = route('index');

        if ($request->filled('redirect'))
        {
            switch ($request->redirect)
            {
                case 'page.show':
                    $redirect = route($request->redirect, [
                        'slug' => $comment->page->slug
                    ]);
                break;
                case 'thread.show':
                    $redirect = route($request->redirect, [
                        'category_slug' => $comment->thread->topic->category->slug,
                        'topic_slug' => $comment->thread->topic->slug,
                        'thread_slug' => $comment->thread->slug
                    ]);
                break;
            }
        }

        return $redirect;
    }
}
