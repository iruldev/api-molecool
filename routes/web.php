<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'api'], function () use ($router) {

    // Route Auth
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');

    $router->group(['middleware' => ['auth']], function () use ($router) {
        // Route Comment
        $router->post('comment/{article_id}', 'ArticleController@comment');

        // Route Detail
        $router->get('article/{id}', 'ArticleController@show');

        $router->group(['middleware' => ['admin']], function () use ($router) {
            // Route Users
            $router->get('users', 'UserController@allUsers');
            $router->put('profile', 'UserController@profile');
            $router->delete('users/{id}', 'UserController@singleUser');

            // Route Article
            $router->get('articles', 'ArticleController@index');
            $router->post('article', 'ArticleController@store');
            $router->put('article/{id}', 'ArticleController@update');
            $router->delete('article/{id}', 'ArticleController@destroy');
        });
    });
});
