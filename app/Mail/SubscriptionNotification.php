<?php

namespace App\Mail;

use App\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The comment instance.
     * 
     * @var Comment
     */
    public $comment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $admin = \App\User::where('is_admin', 1)->firstOrFail();

        return $this
            ->from($admin->email, config('app_name', 'Converse'))
            ->subject(__('Subscription Notification: New comment posted on ":thread"', ['thread' => $this->comment->thread->title]))
            ->view('emails.subscription')
            ->with('comment', $this->comment);
    }
}
