<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laratrust\Models\Team as LaratrustTeam;

class Profile extends LaratrustTeam
{
    protected $fillable = ['name'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
