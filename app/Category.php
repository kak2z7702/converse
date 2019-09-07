<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
    ];

    /**
     * The topics related to the category.
     */
    public function topics()
    {
        return $this->hasMany('App\Topic');
    }

    /**
     * The user who created the category.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the category last topic order.
     *
     * @return number
     */
    public function getLastTopicOrderAttribute()
    {
        return \App\Topic::where('category_id', $this->id)->max('order');
    }
}
