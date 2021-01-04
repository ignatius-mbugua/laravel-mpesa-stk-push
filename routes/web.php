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

Route::get('/', 'PaymentController@index')->name('homepage');
Route::post('make-payment', 'PaymentController@payment')->name('make-payment');
Route::get('transactions', 'PaymentController@transactions')->name('transactions');
Route::post('callback-payment', 'PaymentController@mpesaCallback');
