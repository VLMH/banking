<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all()->getDictionary();

        $result = array_reduce($users, function($carry, $user) {
            $carry[$user->id] = $user->hkid;
            return $carry;
        }, []);
        return response($result, 200);
    }
}
