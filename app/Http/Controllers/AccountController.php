<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Account;

class AccountController extends Controller
{
    /**
     * GET /users/{id}/accounts
     * List out accounts of a user
     */
    public function index(Request $req)
    {
        // find user with active accounts
        $user = User::find($req->userId);
        $accounts = $user->activeAccounts()->get();

        // response
        $resp = [
            'id'       => $user->id,
            'hkid'     => $user->hkid,
            'accounts' => $accounts->pluck('id'),
        ];
        return response($resp, 200);
    }

    /**
     * POST /users/{id}/accounts
     * Create account for a user
     */
    public function create(Request $req)
    {
        // find user
        $user = User::find($req->userId);

        // create account
        $account = $user->accounts()->create([]);

        // response
        return response(['id' => $account->id], 201);
    }
}
