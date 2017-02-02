<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return [
      'user' => [
        'GET /users',
        'GET /users/{id}',
        'POST /users',
      ],
      'account' => [
        'GET /users/{id}/accounts',
        'GET /users/{id}/accounts/{accountId}',
        'POST /users/{id}/accounts',
        'DELETE /users/{id}/accounts/{accountId}',
        'POST /users/{id}/accounts/{accountId}/deposit',
        'POST /users/{id}/accounts/{accountId}/withdraw',
        'POST /users/{id}/accounts/{accountId}/transfer',
      ]
    ];
});
