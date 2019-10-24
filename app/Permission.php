<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug'
    ];

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
