<?php

namespace App\Listeners;

use App\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddNormalUserRoles
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
        if (!$event->user->is_admin)
        {
            $thread_management_role = Role::findOrFail(2);
            $comment_management_role = Role::findOrFail(3);

            $event->user->roles()->attach($thread_management_role);
            $event->user->roles()->attach($comment_management_role);
        }
    }
}
