<?php

namespace App\Policies;

use App\User;
use App\Thread;
use Illuminate\Auth\Access\HandlesAuthorization;

class ThreadPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any threads.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the thread.
     *
     * @param  \App\User  $user
     * @param  \App\Thread  $thread
     * @return mixed
     */
    public function view(User $user, Thread $thread)
    {
        //
    }

    /**
     * Determine whether the user can create threads.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        return $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'thread_create');
        })->first() !== null;
    }

    /**
     * Determine whether the user can update the thread.
     *
     * @param  \App\User  $user
     * @param  \App\Thread  $thread
     * @return mixed
     */
    public function update(User $user, Thread $thread)
    {
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        $is_owner = $user->id === $thread->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'thread_update');
        })->first() !== null;
        
        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($thread) {
            $query->where('slug', 'topic_management_' . $thread->topic->hash);
        })->first() !== null;

        $has_topic_role = $user->roles()->where('slug', 'topic_management')->first() !== null;

        return ($is_owner && $has_permission) || ($has_topic_permission || $has_topic_role);
    }

    /**
     * Determine whether the user can delete the thread.
     *
     * @param  \App\User  $user
     * @param  \App\Thread  $thread
     * @return mixed
     */
    public function delete(User $user, Thread $thread)
    {
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        $is_owner = $user->id === $thread->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'thread_delete');
        })->first() !== null;
        
        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($thread) {
            $query->where('slug', 'topic_management_' . $thread->topic->hash);
        })->first() !== null;

        $has_topic_role = $user->roles()->where('slug', 'topic_management')->first() !== null;

        return ($is_owner && $has_permission) || ($has_topic_permission || $has_topic_role);
    }

    /**
     * Determine whether the user can restore the thread.
     *
     * @param  \App\User  $user
     * @param  \App\Thread  $thread
     * @return mixed
     */
    public function restore(User $user, Thread $thread)
    {
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        $is_owner = $user->id === $thread->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'thread_restore');
        })->first() !== null;
        
        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($thread) {
            $query->where('slug', 'topic_management_' . $thread->topic->hash);
        })->first() !== null;

        $has_topic_role = $user->roles()->where('slug', 'topic_management')->first() !== null;

        return ($is_owner && $has_permission) || ($has_topic_permission || $has_topic_role);
    }

    /**
     * Determine whether the user can permanently delete the thread.
     *
     * @param  \App\User  $user
     * @param  \App\Thread  $thread
     * @return mixed
     */
    public function forceDelete(User $user, Thread $thread)
    {
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        $is_owner = $user->id === $thread->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'thread_force_delete');
        })->first() !== null;
        
        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($thread) {
            $query->where('slug', 'topic_management_' . $thread->topic->hash);
        })->first() !== null;

        $has_topic_role = $user->roles()->where('slug', 'topic_management')->first() !== null;

        return ($is_owner && $has_permission) || ($has_topic_permission || $has_topic_role);
    }

    /**
     * Determine whether the user can open/close the thread.
     *
     * @param  \App\User  $user
     * @param  \App\Thread  $thread
     * @return mixed
     */
    public function open(User $user, Thread $thread)
    {
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($thread) {
            $query->where('slug', 'topic_management_' . $thread->topic->hash);
        })->first() !== null;

        $has_topic_role = $user->roles()->where('slug', 'topic_management')->first() !== null;

        return $has_topic_permission || $has_topic_role;
    }

    /**
     * Determine whether the user can post a comment on the thread.
     *
     * @param  \App\User  $user
     * @param  \App\Thread  $thread
     * @return mixed
     */
    public function comment(User $user, Thread $thread)
    {
        return $thread->is_open;
    }
}
