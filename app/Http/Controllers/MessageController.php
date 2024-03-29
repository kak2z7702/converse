<?php

namespace App\Http\Controllers;

use App\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    /**
     * Show the messages management page.
     *
     * @param $request Incoming request.
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', 'App\Message');

        $messages = null;

        if (!$request->filled('q'))
        {
            $messages = Message::where('user_id', auth()->user()->id)
                ->orWhere('receiver_id', auth()->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate();
        }
        else
        {
            $data = $request->validate(['q' => 'string|max:256']);

            $messages = Message::where('title', 'like', $data['q'] . '%')
                ->where('user_id', auth()->user()->id)
                ->orWhere('receiver_id', auth()->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate();

            $messages->appends(['q' => request()->q]);
        } 

        return view($this->findView('message.index'), ['messages' => $messages]);
    }

    /**
     * Show the message.
     *
     * @param $message Message id.
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show($message)
    {
        $message = Message::where('id', $message)->firstOrFail();

        $message->is_seen = true;

        $message->save();

        return view($this->findView('message.show'), ['message' => $message]);
    }

    /**
     * Show the message form to create a message.
     * 
     * @param $request Incoming request.
     */
    public function create(Request $request)
    {
        $this->authorize('create', 'App\Message');

        if ($request->isMethod('get'))
        {
            $users = \App\User::where('id', '!=', auth()->user()->id)->orderBy('name', 'asc')->get();

            return view($this->findView('message.form'), [
                'title' => $request->title,
                'users' => $users,
                'receiver' => $request->receiver,
                'redirect' => route('message.index')
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(), [
                'title' => ['required', 'string', 'max:255'],
                'content' => ['required', 'string'],
                'receiver' => ['required', 'numeric', 'exists:users,id', function ($attribute, $value, $fail) {
                    if ($value === auth()->user()->id) return $fail(__('The current receiver is incorrect.'));
                }]
            ]);

            if ($validator->fails())
            {
                return redirect()
                    ->route('message.create', [
                            'title' => $request->title,
                            'receiver' => $request->receiver
                        ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $message = new Message($data);
            $message->receiver_id = $data['receiver'];
            $message->user_id = auth()->user()->id;

            $message->save();

            return view($this->findView('result'), [
                'message' => __('Message was created successfully.'),
                'redirect' => route('message.index')
            ]);
        }
    }

    /**
     * Show the message form to update a message.
     * 
     * @param $request Incoming request.
     * @param $message Message id.
     */
    public function update(Request $request, $message)
    {
        $message = Message::lockForUpdate()->findOrFail($message);

        $this->authorize('update', $message);

        if ($request->isMethod('get'))
        {
            return view($this->findView('message.form'), [
                'message' => $message,
                'redirect' => $this->getRedirect($request, $message)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            if ($validator->fails())
            {
                return redirect()
                    ->route('message.update', [
                            'message' => $message->id,
                            'redirect' => $request->filled('redirect') ? $request->redirect : 'index'
                        ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->valid();

            $message->fill($data);

            $message->is_seen = false;

            $message->save();

            return view($this->findView('result'), [
                'message' => __('Message was updated successfully.'),
                'redirect' => $this->getRedirect($request, $message)
            ]);
        }
    }

    /**
     * Delete a message.
     * 
     * @param $request Incoming request.
     * @param $message Message id.
     */
    public function delete(Request $request, $message = null)
    {
        if ($request->isMethod('get'))
        {
            $message = Message::findOrFail($message);

            $this->authorize('delete', $message);
    
            $message->delete();
    
            return view($this->findView('result'), [
                'message' => __('Message was deleted successfully.'),
                'redirect' => $this->getRedirect($request, $message)
            ]);
        }
        else if ($request->isMethod('post'))
        {
            $data = $request->validate([
                'messages' => 'required|regex:/[0-9]+,?/i'
            ]);

            $messages = Message::whereIn('id', explode(",", $data['messages']))->get();

            foreach ($messages as $message)
                $this->authorize('delete', $message);

            Message::whereIn('id', $messages->pluck('id'))->delete();

            return view($this->findView('result'), [
                'message' => __('Messages were deleted successfully.'),
                'redirect' => route('message.index')
            ]);
        }
    }

    /**
     * Get redirection link.
     * 
     * @param $request Incoming request.
     * @param $message Current message.
     * @return string
     */
    private function getRedirect(Request $request, Message $message)
    {
        $redirect = route('index');

        if ($request->filled('redirect'))
        {
            switch ($request->redirect)
            {
                case 'message.index':
                    $redirect = route($request->redirect);
                break;
                case 'message.show':
                    $redirect = route($request->redirect, ['message' => $message->id]);
                break;
            }
        }

        return $redirect;
    }
}
