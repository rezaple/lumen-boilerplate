<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => ['cors','throttle:60']], function () use ($router) {

    $router->post('oauth/token', 'Auth\AccessTokenController@issueToken');
    
    $router->group([
        'namespace' => 'Auth',
        'as' => 'auth',
        'prefix' => 'v1'
    ], function () use ($router) {

        $router->post('login', [
            'uses'       => 'LoginController@login'
        ]);

        $router->post('register', [
            'uses'       => 'RegisterController@register'
        ]);

        $router->post('/password/reset', 'ResetPasswordController@postEmail');
        $router->post('/password/reset/{token}',['as' => 'password.reset','uses'=>'ResetPasswordController@postReset']);

        $router->get('register/verify/{confirmationCode}', [
            'as' => 'confirmation_path',
            'uses' => 'RegisterController@confirm'
        ]);
    });
});

$router->group(['middleware' => ['cors','auth:api', 'throttle:60']], function () use ($router) {
    $router->group([
        'namespace' => 'Auth',
        'as' => 'auth',
        'prefix' => 'v1'
    ], function () use ($router) {

        $router->get('logout', [
            'uses'       => 'LoginController@logout'
        ]);

        $router->post('refresh', [
            'uses'       => 'LoginController@refresh'
        ]);
    });
});

$router->group(['middleware' => ['cors','auth:api', 'throttle:60','scope:staff']], function () use ($router) {

    $router->group([
        'namespace' => 'Staff',
        'as' => 'staff',
        'prefix' => 'v1'
    ], function () use ($router) {

        $router->get('test', function () {
            return 'Hello staff';
        });
    });
});

$router->group(['middleware' => ['cors','auth:api', 'throttle:60','scope:admin']], function () use ($router) {
    $router->group([
        'namespace' => 'Admin',
        'as' => 'admin',
        'prefix' => 'v1'
    ], function () use ($router) {

        $router->get('testa', function () {
            return 'hello admin';
        });

    });
});


$router->group(['middleware' => 'cors'], function () use ($router) {
	$router->group(['prefix' => 'v1'], function () use ($router) {
        //route here
    });

    //for test view email
    // $router->get('/mail/register', 'MailController@testSendMailRegister');
    // $router->get('/mail/forgot', 'MailController@testSendMailForgot');
});