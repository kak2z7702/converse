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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_open' => 'boolean',
        'is_pinned' => 'boolean'
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

    /**
     * The subscriptions related to the thread.
     */
    public function subscriptions()
    {
        return $this->hasMany('App\Subscription');
    }

    /**
     * The favorites related to the thread.
     */
    public function favorites()
    {
        return $this->hasMany('App\Favorite');
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
