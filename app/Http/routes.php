<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// HOME PAGE
Route::group(['middleware' => 'auth'], function() {
    Route::get('/', 'HomeController@index');
    // Force load angular's index /public/index.html
    //Route::get('/', function () { return File::get(public_path() . '/index.html'); });
});

// Authentication routes...
// default views = /resources/views/auth/*.blade.php
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
// default views = /resources/views/auth/*.blade.php
Route::group(['middleware' => 'ip.filter'], function() {
    Route::get('auth/register', 'Auth\AuthController@getRegister');
    Route::post('auth/register', 'Auth\AuthController@postRegister');
});

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');
// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

// route /oauth2callback?state=1513641807&code=4/rXEnwhg9ceFdaOlXZFReTNnWoWWcS60zClSEuZR5Ff4
Route::get('oauth2callback', 'OauthController@callback');

// API ROUTES /api/v1/*
Route::group(['prefix' => 'api/album/v1', 'middleware' => 'cors'], function()
{
    Route::get('dirs/{dirs}/rescan', 'DirectoryController@rescan');
    Route::get('dirs/{dirs}/banner', 'DirectoryController@banner');
    Route::post('dirs/create', 'DirectoryController@create');
    Route::post('dirs/{dirs}/setup', 'DirectoryController@setup');
    // RESTful Resource
    Route::resource('dirs', 'DirectoryController');
    // RESTful Resource
    Route::resource('dirs.files', 'FileController');
});

// Albums
// php artisan route:list --path "albums"
Route::group(['prefix' => 'albums', 'middleware' => 'auth'], function(){
    // Album manipulation
    Route::get('/', [
        'as' => 'index',
        'uses' => 'AlbumsController@index'
    ]);

    Route::get('createalbum', [
        'as' => 'create_album_form',
        'uses' => 'AlbumsController@create'
    ]);
    Route::post('createalbum', [
        'as' => 'create_album',
        'uses' => 'AlbumsController@store'
    ]);
    Route::get('editalbum/{id}', [
        'as' => 'edit_album_form',
        'uses' => 'AlbumsController@edit'
    ]);
    Route::post('editalbum/{id}', [
        'as' => 'edit_album',
        'uses' => 'AlbumsController@update'
    ]);
    Route::get('deletealbum/{id}', [
        'as' => 'delete_album',
        'uses' => 'AlbumsController@destroy'
    ]);
    Route::post('sortalbumimages/{id}', [
        'as' => 'sort_album_images',
        'uses' => 'AlbumsController@sortImages'
    ]);
    Route::get('createbanner/{id}', [
        'as' => 'create_album_banner',
        'uses' => 'AlbumsController@banner'
    ]);

    Route::get('result/{id}', [
        'as' => 'show_album_result',
        'uses' => 'AlbumsController@result'
    ]);

    Route::get('home', 'AlbumsController@home');
    Route::post('home', 'AlbumsController@homeUpdate');
    Route::post('homeadd', 'AlbumsController@homeAdd');
    Route::get('infos', 'AlbumsController@infos');
    Route::get('relnews/{id}', 'AlbumsController@relnews')->where('id', '[0-9]+');
    Route::get('relnews', 'AlbumsController@relnews');
    Route::post('relnewsadd', 'AlbumsController@relnewsAdd');
    Route::post('relnews', 'AlbumsController@relnewsUpdate');

    Route::get('{id}', [
        'as' => 'show_album',
        'uses' => 'AlbumsController@show'
    ])->where('id', '[0-9]+');

    // Image manipulation
    Route::get('addimage/{id}', [
        'as' => 'add_image',
        'uses' => 'ImagesController@create'
    ]);
    Route::post('addimage', [
        'as' => 'add_image_to_album',
        'uses' => 'ImagesController@store'
    ]);
    Route::get('editimage/{id}', [
        'as' => 'edit_image_form',
        'uses' => 'ImagesController@edit'
    ]);
    Route::post('editimage', [
        'as' => 'edit_image',
        'uses' => 'ImagesController@update'
    ]);
    Route::get('deleteimage/{id}', [
        'as' => 'delete_image',
        'uses' => 'ImagesController@destroy'
    ]);
    Route::post('moveimage', [
        'as' => 'move_image',
        'uses' => 'ImagesController@move'
    ]);
    Route::post('deleteimages', [
        'as' => 'delete_images',
        'uses' => 'ImagesController@destroyMany'
    ]);
    Route::post('moveimages', [
        'as' => 'move_images',
        'uses' => 'ImagesController@moveMany'
    ]);
    Route::post('sortimage', [
        'as' => 'sort_image',
        'uses' => 'ImagesController@sort'
    ]);
});

// Comments
Route::group(['prefix' => 'comments', 'middleware' => 'auth'], function()
{
    Route::get('/', 'CommentsController@index');
});

// 라이브 영상
Route::get('/livevideos/comments', 'LiveVideos\CommentsController@index');
Route::group(['prefix' => 'livevideos', 'middleware' => 'auth'], function ()
{
    // 영상 자막 배경 관리
    Route::get('/captions/backgrounds', 'LiveVideos\CaptionsBackgroundsController@index');
    // 영상 하단 공지
    Route::get('/notice', 'LiveVideos\Notice\LiveVideosNoticeController@index');
    Route::post('/notice', 'LiveVideos\Notice\LiveVideosNoticeController@store');
    // 영상 BREAD (Browse, Read, Edit, Add, Delete)
    Route::resource('/', 'LiveVideosController');
});

// Youtube
Route::group(['prefix' => 'youtube', 'middleware' => 'auth'], function()
{
    Route::get('/', 'YoutubeController@index');
    Route::get('playlistitems/{id}', 'YoutubeController@playlistItems');
});

// 개발용 서버 라우팅
if (env('APP_ENV') == 'local') {
    // Debug bar
    Route::get('/_debugbar/assets/stylesheets', [
        'as' => 'debugbar-css',
        'uses' => '\Barryvdh\Debugbar\Controllers\AssetController@css'
    ]);
    Route::get('/_debugbar/assets/javascript', [
        'as' => 'debugbar-js',
        'uses' => '\Barryvdh\Debugbar\Controllers\AssetController@js'
    ]);
    Route::get('/_debugbar/open', [
        'as' => 'debugbar-open',
        'uses' => '\Barryvdh\Debugbar\Controllers\OpenController@handler'
    ]);
}

/*// Catch all undefined routes for AngularJS.
Route::any('{path?}', function($path) {
    return File::get(public_path() .'/'. $path);
})->where('path', '.+');*/
