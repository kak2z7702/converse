<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description'
    ];

    /**
     * The category that owns the topic.
     */
    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    /**
     * The threads related to the topic.
     */
    public function threads()
    {
        return $this->hasMany('App\Thread');
    }

    /**
     * The user who created the topic.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
