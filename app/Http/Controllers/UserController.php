<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\User;

class UserController extends Controller
{
    /**
     * GET /users
     * List out all users
     */
    public function index()
    {
        $users = User::all()->getDictionary();

        $result = array_reduce($users, function($carry, $user) {
            $carry[$user->id] = $user->hkid;
            return $carry;
        }, []);
        return response($result, 200);
    }

    /**
     * POST /users
     * Create user
     *
     * @param  Request $req
     */
    public function create(Request $req)
    {
        // validate hkid
        $validationRules = [
            'hkid' => 'required|unique:user',
        ];
        $errorMessages = [
            'required' => 'Invalid :attribute',
            'unique' => ':attribute has already in use',
        ];
        $validator = Validator::make($req->all(), $validationRules, $errorMessages);
        if ($validator->fails()) {
            return response(['message' => $validator->errors()->first('hkid')], 400);
        }

        // create user
        $user = User::create(['hkid' => $req->hkid]);

        // response with userId
        return response(['id' => $user->id], 201);
    }
}
