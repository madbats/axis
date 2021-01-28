<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DisplayController;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\Input;

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

Route::get('/', "SearchController@home")->name('home');
//
//Route::get('/dblp','ApiCallerTest@callDblp')->name('dblp');
//
//Route::get('/crossref','ApiCallerTest@callCrossRef')->name('crossref');
//
//Route::get('/s2','ApiCallerTest@callS2')->name('s2');
//
//Route::get('/core','ApiCallerTest@callCore')->name('core');
//
//Route::get('/gender','ApiCallerTest@callGenderize')->name('gender');
//
Route::get('/search', 'SearchController@search')->name('search');

Route::get('/article/{id}', 'DisplayController@displayArticle')->name('article');

Route::match(['get','post'], '/analytics', 'GraphController@drawGraph')->name('analytics');

Route::match(['get','post'], '/statistics', 'StatisticsController@index')->name('statistics');

Route::get('/getAuthorsbyConference', 'StatisticsController@getAuthorsbyConference');

Route::get('/getArticlesByConference', 'StatisticsController@getArticlesByConference');

Route::get('/test', 'CrawlingController@crawler');
