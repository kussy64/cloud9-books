<?php
use App\Book;
use Illuminate\Http\Request; 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BooksController;


//本ダッシュボード表示
//Route::get('/', [BooksController::class, 'index'])->name('books');

Route::get('/', [BooksController::class, 'index'])->name('books.index');
//登録処理
Route::post('/booksregister','BooksController@register');

Route::post('/books/store','BooksController@store');

//更新画面
Route::get('/booksedit/{books}', 'BooksController@edit')->name('booksedit');
Route::post('/booksedit/{books}','BooksController@edit');

//更新処理
Route::post('/books/update','BooksController@update');

//CSVアップロード

Route::post('books/ajax_store', 'BooksController@ajax_store');
//CSVダウンロード
Route::get('/csv', [BooksController::class, 'postCsv'])->name('books.postCsv');

//本を削除
Route::delete('/book/{book}','BooksController@destroy');

//Auth
Auth::routes();
Route::get('/home', 'BooksController@index')->name('home');

