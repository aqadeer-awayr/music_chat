<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('test', 'Controller@test');
Route::get('/clear', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    exec('composer dump-autoload');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    // Artisan::call('migrate');
    // $list = Artisan::call('list');
    // dd($list);
    Artisan::call('websockets:restart');
    echo 'execute';
    //Artisan::call('websockets:serve');

    return "Cleared!";
});
