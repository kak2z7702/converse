<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title'
    ];

    /**
     * The topic that owns the thread.
     */
    public function topic()
    {
        return $this->belongsTo('App\Topic');
    }

    /**
     * The comments related to the thread.
     */
    public function comments()
    {
        return $this->morphMany('App\Comment', 'entity');
    }

    /**
     * The user who created the thread.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
