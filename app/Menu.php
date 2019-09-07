<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'url'
    ];

    /**
     * The user who created the menu.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
