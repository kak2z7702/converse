<?php

namespace App\Listeners;

use App\Comment;
use App\Events\CommentPosted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CommentPosted  $event
     * @return void
     */
    public function handle(CommentPosted $event)
    {
        if ($event->comment->entity_type == 'App\Thread')
        {
            $subscribed_users = $event->comment->thread->subscriptions()->pluck('user_id');

            if ($subscribed_users->isNotEmpty())
            {
                $recipients = \App\User::whereIn('id', $subscribed_users)->get();

                Mail::to($recipients)->queue(new \App\Mail\SubscriptionNotification($event->comment));
            }
        }
    }
}
