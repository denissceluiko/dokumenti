<?php

use App\Http\Controllers\Document\TemplateController;
use Illuminate\Support\Facades\Route;

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

Route::resource('template', TemplateController::class, ['only' => ['create', 'store', 'show']]);
Route::group(['prefix' => 'template', 'as' => 'template.'], function() {
    Route::post('{template}/compile', [TemplateController::class, 'compile'])->name('compile');
});
