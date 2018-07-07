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

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('my-ip/', 'IdentificationController@getMyIp');
$app->post('network-ip/', 'IdentificationController@postNetworkIp');

$app->post('file-upload/', 'FileController@uploadFile');
$app->get('read-file/', 'FileController@getMyFile');

$app->post('save-file/', 'FileStorageController@saveFile');
$app->get('get-file/', 'FileStorageController@getFile');


