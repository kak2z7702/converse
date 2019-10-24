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

    /**
     * Created at accessor.
     * 
     * @param $value Field value.
     */
    public function getCreatedAtAttribute($value)
    {
        if (auth()->check()) 
            return \Carbon::createFromFormat('Y-m-d H:i:s', $value, config('app.timezone'))->setTimezone(auth()->user()->timezone);

        return new \Carbon($value);
    }

    /**
     * Updated at accessor.
     * 
     * @param $value Field value.
     */
    public function getUpdatedAtAttribute($value)
    {
        if (auth()->check()) 
            return \Carbon::createFromFormat('Y-m-d H:i:s', $value, config('app.timezone'))->setTimezone(auth()->user()->timezone);

        return new \Carbon($value);
    }
}
