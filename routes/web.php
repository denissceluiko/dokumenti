<?php

use App\Http\Controllers\Document\DocumentController;
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
    return redirect()->route('template.index');
});

Route::resource('template', TemplateController::class, ['except' => ['delete']]);
Route::group(['prefix' => 'template', 'as' => 'template.'], function() {
    Route::post('{template}/compile', [TemplateController::class, 'compile'])->name('compile');
    Route::post('{template}/batch', [TemplateController::class, 'batch'])->name('batch');
    Route::get('{template}/resolve', [TemplateController::class, 'resolve'])->name('resolve');
    Route::get('{template}/download', [TemplateController::class, 'download'])->name('download');
    Route::get('{template}/excel', [TemplateController::class, 'excel'])->name('excel');
});

Route::resource('document', DocumentController::class, ['except' => ['create', 'store']]);
Route::group(['prefix' => 'document', 'as' => 'document.'], function() {
    Route::get('{document}/clone', [DocumentController::class, 'clone'])->name('clone');
    Route::get('{document}/download', [DocumentController::class, 'download'])->name('download');
});