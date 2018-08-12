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
    return \Illuminate\Support\Facades\File::get(app()->basePath('public/search.html'));
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'contents'], function () use ($router) {
        $router->get('/', 'ApiController@contents');
        $router->post('/search', 'ApiController@search');
    });
    $router->post('upsert/{cid}', 'ApiController@upsert');
    $router->get('stats', 'ApiController@stats');

});