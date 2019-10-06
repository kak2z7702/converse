<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'email', 
        'show_email',
        'password', 
        'badge', 
        'bio'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'is_banned' => 'boolean',
        'show_email' => 'boolean'
    ];

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role', 'role_user');
    }

    /**
     * The subscriptions related to the user.
     */
    public function subscriptions()
    {
        return $this->hasMany('App\Subscription');
    }

    /**
     * The favorites related to the user.
     */
    public function favorites()
    {
        return $this->hasMany('App\Favorite');
    }

    /**
     * Show email mutator.
     * 
     * @param $value Incoming value.
     */
    public function setShowEmailAttribute($value)
    {
        $this->attributes['show_email'] = is_bool($value) ? $value : ($value == 'on');
    }
}
