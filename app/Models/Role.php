<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laratrust\Models\Role as LaratrustRole;

class Role extends LaratrustRole
{
    protected $fillable = ['name'];

    public function profiles()
    {
        return $this->belongsToMany(Profile::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
