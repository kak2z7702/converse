<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    /**
     * The permission that belongs to the permission role.
     */
    public function permission()
    {
        return $this->belongsTo('App\Permission');
    }

    /**
     * The role that belongs to the permission role.
     */
    public function role()
    {
        return $this->belongsTo('App\Role');
    }
}
