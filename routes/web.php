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

$router->group([
	'prefix' => 'api/v1',
], function ($router) {
	$router->get('/me', 'UserController@me');
	$router->get('/user', 'UserController@all');
	$router->post('/login', 'UserController@login');

	$router->get('/message', 'MessageController@all');
	$router->post('/message', 'MessageController@create');
	$router->put('/message/{messageId}', 'MessageController@update');
	$router->delete('/message/{messageId}', 'MessageController@delete');

});