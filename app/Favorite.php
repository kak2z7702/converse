<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'thread_id',
        'user_id'
    ];


    /**
     * The thread that belongs to the favorite.
     */
    public function thread()
    {
        return $this->belongsTo('App\Thread');
    }

    /**
     * The user that belongs to the favorite.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
