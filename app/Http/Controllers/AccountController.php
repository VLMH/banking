<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

use App\User;
use App\Account;

class AccountController extends Controller
{
    /**
     * GET /users/{user}/accounts
     * List out accounts of a user
     */
    public function index(Request $req, User $user)
    {
        // find user with active accounts
        $accounts = $user->accounts()->get();

        // response
        $resp = [
            'id'       => $user->id,
            'hkid'     => $user->hkid,
            'accounts' => $accounts->pluck('id'),
        ];
        return response($resp, 200);
    }

    /**
     * GET /users/{userId}/accounts/{accountId}
     * Retrieve an account with balance
     */
    public function show(Request $req, User $user, Account $account)
    {
        // response
        return response([
            'id' => $account->id,
            'balance' => '$' . number_format($account->balance(), 2),
        ], 200);
    }

    /**
     * POST /users/{id}/accounts
     * Create account for a user
     */
    public function create(Request $req, User $user)
    {
        // create account
        $account = $user->accounts()->create([]);

        // response
        return response(['id' => $account->id], 201);
    }

    /**
     * DELETE /users/{userId}/accounts/{accountId}
     * Close an account
     */
    public function destroy(Request $req, User $user, Account $account)
    {
        $account->delete();

        // response
        return response(null, 200);
    }

    /**
     * POST /users/{userId}/accounts/{accountId}/deposit
     * Account deposit
     */
    public function deposit(Request $req, User $user, Account $account)
    {
        // validation
        $validationRules = [
            'amount' => 'required|numeric|min:0.01',
        ];
        $errorMessages = [
            'required' => 'Invalid :attribute',
            'numeric'  => ':attribute must be a number',
            'min'      => ':attribute must greater than :min'
        ];
        $validator = Validator::make($req->all(), $validationRules, $errorMessages);
        if ($validator->fails()) {
            return response(['message' => $validator->errors()->first('amount')], 400);
        }

        $account->deposit($req->amount)->save();

        return response(null, 200);
    }
}
