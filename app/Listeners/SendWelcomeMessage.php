<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeMessage
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
     * @param  Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $message = new \App\Message([
            'title' => __('Welcome to the community!'),
            'content' => __('You are great! Be sure to check the rules :)'),
        ]);

        $message->receiver_id = $event->user->id;
        $message->user_id = 1;

        $message->save();
    }
}
