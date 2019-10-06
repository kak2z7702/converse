<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
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
     * The thread that belongs to the subscription.
     */
    public function thread()
    {
        return $this->belongsTo('App\Thread');
    }

    /**
     * The user that belongs to the subscription.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
