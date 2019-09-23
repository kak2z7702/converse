<?php

namespace App\Policies;

use App\User;
use App\Comment;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any comments.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the comment.
     *
     * @param  \App\User  $user
     * @param  \App\Comment  $comment
     * @return mixed
     */
    public function view(User $user, Comment $comment)
    {
        //
    }

    /**
     * Determine whether the user can create comments.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        return $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'comment_create');
        })->first() !== null;
    }

    /**
     * Determine whether the user can update the comment.
     *
     * @param  \App\User  $user
     * @param  \App\Comment  $comment
     * @return mixed
     */
    public function update(User $user, Comment $comment)
    {
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        $is_owner = $user->id === $comment->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'comment_update');
        })->first() !== null;
        
        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($comment) {
            $query->where('slug', 'topic_management_' . $comment->thread->topic->hash);
        })->first() !== null;

        return ($is_owner && $has_permission) || $has_topic_permission;
    }

    /**
     * Determine whether the user can delete the comment.
     *
     * @param  \App\User  $user
     * @param  \App\Comment  $comment
     * @return mixed
     */
    public function delete(User $user, Comment $comment)
    {
        if ($comment->is_original) return false;
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        $is_owner = $user->id === $comment->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'comment_delete');
        })->first() !== null;
        
        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($comment) {
            $query->where('slug', 'topic_management_' . $comment->thread->topic->hash);
        })->first() !== null;

        return ($is_owner && $has_permission) || $has_topic_permission;
    }

    /**
     * Determine whether the user can restore the comment.
     *
     * @param  \App\User  $user
     * @param  \App\Comment  $comment
     * @return mixed
     */
    public function restore(User $user, Comment $comment)
    {
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        $is_owner = $user->id === $comment->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'comment_restore');
        })->first() !== null;
        
        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($comment) {
            $query->where('slug', 'topic_management_' . $comment->thread->topic->hash);
        })->first() !== null;

        return ($is_owner && $has_permission) || $has_topic_permission;
    }

    /**
     * Determine whether the user can permanently delete the comment.
     *
     * @param  \App\User  $user
     * @param  \App\Comment  $comment
     * @return mixed
     */
    public function forceDelete(User $user, Comment $comment)
    {
        if ($user->is_admin) return true;
        if ($user->is_banned) return false;

        $is_owner = $user->id === $comment->user_id;

        $has_permission = $user->roles()->whereHas('permissions', function($query) {
            $query->where('slug', 'comment_force_delete');
        })->first() !== null;
        
        $has_topic_permission = $user->roles()->whereHas('permissions', function($query) use ($comment) {
            $query->where('slug', 'topic_management_' . $comment->thread->topic->hash);
        })->first() !== null;

        return ($is_owner && $has_permission) || $has_topic_permission;
    }
}
