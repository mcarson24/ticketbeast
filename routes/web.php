<?php

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

Route::get('concerts/{id}', 'ConcertsController@show')->name('concerts.show');
Route::post('concerts/{id}/orders', 'ConcertOrdersController@store');
Route::get('orders/{confirmation_number}', 'OrdersController@show');

Route::get('login', 'Auth\LoginController@showLoginForm');
Route::get('backstage/login', 'Auth\LoginController@showLoginForm');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('auth.logout');

Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], function() {
	Route::get('concerts', 'ConcertsController@index')->name('backstage.concerts.index');
	Route::post('concerts', 'ConcertsController@store');
	Route::get('concerts/new', 'ConcertsController@create')->name('backstage.concerts.new');
	Route::get('concerts/{id}/edit', 'ConcertsController@edit')->name('backstage.concerts.edit');
	Route::patch('concerts/{id}', 'ConcertsController@update')->name('backstage.concerts.update');
	Route::post('published-concerts', 'PublishedConcertsController@store')->name('backstage.published-concerts.store');
	Route::get('published-concerts/{id}/orders', 'PublishedConcertsOrdersController@index')->name('backstage.published-concert-orders.index');
	Route::get('concerts/{id}/messages/new', 'ConcertMessagesController@create')->name('backstage.concert-messages.create');
});

