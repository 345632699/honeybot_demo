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

$router->get('/user/{robot_id}','Robot\UserController@getHxId');
$router->get('/relationship/{r_uid}/{f_uid}','Robot\UserController@updateRelationship');
$router->get('/get_relation/{robot_id}','Robot\UserController@getFriendship');