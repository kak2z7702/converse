<?php

namespace App\Policies;

use App\User;
use App\Topic;
use Illuminate\Auth\Access\HandlesAuthorization;

class TopicPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any topics.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the topic.
     *
     * @param  \App\User  $user
     * @param  \App\Topic  $topic
     * @return mixed
     */
    public function view(User $user, Topic $topic)
    {
        //
    }

    /**
     * Determine whether the user can create topics.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->is_admin) return true;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'topic_create');
        })->first() !== null;

        return $has_permission;
    }

    /**
     * Determine whether the user can update the topic.
     *
     * @param  \App\User  $user
     * @param  \App\Topic  $topic
     * @return mixed
     */
    public function update(User $user, Topic $topic)
    {
        if ($user->is_admin) return true;

        $is_owner = $user->id == $topic->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'topic_update');
        })->first() !== null;

        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($topic) {
            $query->where('slug', 'topic_management_' . $topic->hash);
        })->first() !== null;

        return ($is_owner && $has_permission) || $has_topic_permission;
    }

    /**
     * Determine whether the user can delete the topic.
     *
     * @param  \App\User  $user
     * @param  \App\Topic  $topic
     * @return mixed
     */
    public function delete(User $user, Topic $topic)
    {
        if ($user->is_admin) return true;

        $is_owner = $user->id == $topic->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'topic_delete');
        })->first() !== null;

        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($topic) {
            $query->where('slug', 'topic_management_' . $topic->hash);
        })->first() !== null;

        return ($is_owner && $has_permission) || $has_topic_permission;
    }

    /**
     * Determine whether the user can restore the topic.
     *
     * @param  \App\User  $user
     * @param  \App\Topic  $topic
     * @return mixed
     */
    public function restore(User $user, Topic $topic)
    {
        if ($user->is_admin) return true;

        $is_owner = $user->id == $topic->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'topic_restore');
        })->first() !== null;

        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($topic) {
            $query->where('slug', 'topic_management_' . $topic->hash);
        })->first() !== null;

        return ($is_owner && $has_permission) || $has_topic_permission;
    }

    /**
     * Determine whether the user can permanently delete the topic.
     *
     * @param  \App\User  $user
     * @param  \App\Topic  $topic
     * @return mixed
     */
    public function forceDelete(User $user, Topic $topic)
    {
        if ($user->is_admin) return true;

        $is_owner = $user->id == $topic->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'topic_force_delete');
        })->first() !== null;

        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($topic) {
            $query->where('slug', 'topic_management_' . $topic->hash);
        })->first() !== null;

        return ($is_owner && $has_permission) || $has_topic_permission;
    }

    /**
     * Determine whether the user can move the topic.
     *
     * @param  \App\User  $user
     * @param  \App\Topic  $topic
     * @return mixed
     */
    public function move(User $user, Topic $topic)
    {
        if ($user->is_admin) return true;

        $is_owner = $user->id == $topic->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'topic_move');
        })->first() !== null;

        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($topic) {
            $query->where('slug', 'topic_management_' . $topic->hash);
        })->first() !== null;

        return ($is_owner && $has_permission) || $has_topic_permission;
    }
}
