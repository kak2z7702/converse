<?php

namespace App\Http\Controllers;

use App\Comment;
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

        $data = $request->validate([
            'content' => 'required|string',
            'thread' => 'required|numeric|exists:threads,id',
        ]);

        $thread = \App\Thread::findOrFail($data['thread']);

        $comment = new Comment($data);
        $comment->user_id = auth()->user()->id;

        $thread->comments()->save($comment);

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
                'content' => 'required|string'
            ]);

            if ($validator->fails())
            {
                return redirect()
                    ->route('comment.update', [
                            'comment' => $comment->id,
                            'redirect' => 'thread.show'
                        ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $thread = \App\Thread::findOrFail($comment->thread_id);

            $comment->fill($data);
            
            $thread->comments()->save($comment);

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

        $comment->delete();

        return view('result', [
            'message' => __('Comment was deleted successfully.'),
            'redirect' => $this->getRedirect($request, $comment)
        ]);
    }

    /**
     * Get redirection link.
     * 
     * @param $request Incoming requets.
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
