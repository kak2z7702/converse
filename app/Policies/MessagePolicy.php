<?php

namespace App\Policies;

use App\User;
use App\Message;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any messages.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the message.
     *
     * @param  \App\User  $user
     * @param  \App\Message  $message
     * @return mixed
     */
    public function view(User $user, Message $message)
    {
        return $user->id === $message->user_id;
    }

    /**
     * Determine whether the user can create messages.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->is_admin) return true;

        return $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'message_create');
        })->first() !== null;
    }

    /**
     * Determine whether the user can update the message.
     *
     * @param  \App\User  $user
     * @param  \App\Message  $message
     * @return mixed
     */
    public function update(User $user, Message $message)
    {
        if ($user->is_admin) return true;

        $is_owner = $user->id === $message->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'message_update');
        })->first() !== null;

        return $is_owner && $has_permission;
    }

    /**
     * Determine whether the user can delete the message.
     *
     * @param  \App\User  $user
     * @param  \App\Message  $message
     * @return mixed
     */
    public function delete(User $user, Message $message)
    {
        if ($user->is_admin) return true;

        $is_owner = $user->id === $message->user_id;
        $is_receiver = $user->id === $message->receiver_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'message_delete');
        })->first() !== null;

        return ($is_owner || $is_receiver) && $has_permission;
    }

    /**
     * Determine whether the user can restore the message.
     *
     * @param  \App\User  $user
     * @param  \App\Message  $message
     * @return mixed
     */
    public function restore(User $user, Message $message)
    {
        if ($user->is_admin) return true;

        $is_owner = $user->id === $message->user_id;
        $is_receiver = $user->id === $message->receiver_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'message_restore');
        })->first() !== null;

        return ($is_owner || $is_receiver) && $has_permission;
    }

    /**
     * Determine whether the user can permanently delete the message.
     *
     * @param  \App\User  $user
     * @param  \App\Message  $message
     * @return mixed
     */
    public function forceDelete(User $user, Message $message)
    {
        if ($user->is_admin) return true;

        $is_owner = $user->id === $message->user_id;
        $is_receiver = $user->id === $message->receiver_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'message_force_delete');
        })->first() !== null;

        return ($is_owner || $is_receiver) && $has_permission;
    }
}
