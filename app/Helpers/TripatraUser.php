<?php

namespace App\Helpers;

use App\Http\Controllers\Api\TripatraAccountController;

class TripatraUser
{
    public static function getAccountNameIds()
    {
        $data = new TripatraAccountController();
        $users = collect($data->getAccounts());
        return $users->pluck('displayName', 'id');
    }
}
