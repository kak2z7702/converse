<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'can_have_comments'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'can_have_comments' => 'boolean'
    ];

    /**
     * The comments related to the page.
     */
    public function comments()
    {
        return $this->morphMany('App\Comment', 'entity');
    }

    /**
     * The user who created the page.
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

    /**
     * Can have comments mutator.
     * 
     * @param $value Incoming value.
     */
    public function setCanHaveCommentsAttribute($value)
    {
        $this->attributes['can_have_comments'] = is_bool($value) ? $value : ($value == 'on');
    }
}
