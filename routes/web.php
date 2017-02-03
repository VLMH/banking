<?php

Route::get('/users', 'UserController@index');
Route::post('/users', 'UserController@create');

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
