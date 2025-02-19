<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class UserHelper
{
    public static function getUserRoleName($module)
    {
        $user = Auth::user();
        $role = $user->load('roles')->roles()->where('module', $module)->first();
        return $role ? $role->role_name : 'user';
    }
}
