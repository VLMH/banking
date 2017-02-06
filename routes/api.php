<?php

Route::get('/users', 'UserController@index');
Route::post('/users', 'UserController@create');

Route::get('/users/{user}/accounts', 'AccountController@index');
Route::get('/users/{user}/accounts/{account}', 'AccountController@show');
Route::post('/users/{user}/accounts', 'AccountController@create');
Route::delete('/users/{user}/accounts/{account}', 'AccountController@destroy');
Route::post('/users/{user}/accounts/{account}/deposit', 'AccountController@deposit');
Route::post('/users/{user}/accounts/{account}/withdraw', 'AccountController@withdraw');

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
