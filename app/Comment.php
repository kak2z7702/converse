<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_original' => 'boolean'
    ];

    /**
     * The page that owns the comment.
     */
    public function page()
    {
        return $this->belongsTo('App\Page', 'entity_id');
    }

    /**
     * The thread that owns the comment.
     */
    public function thread()
    {
        return $this->belongsTo('App\Thread', 'entity_id');
    }

    /**
     * The user who created the comment.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * The entity that the comment is morphing to.
     */
    public function entity()
    {
        return $this->morphTo();
    }
}
