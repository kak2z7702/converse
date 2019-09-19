<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 
        'content'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_seen' => 'boolean'
    ];

    /**
     * The user who is receiving the message.
     */
    public function receiver()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * The user who created the message.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}