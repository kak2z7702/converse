<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    /**
     * The role that belongs to the role user.
     */
    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    /**
     * The user that belongs to the role user.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
