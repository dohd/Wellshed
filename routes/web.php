<?php
/**
 * Global Routes
 */
// Switch between the included languages
use App\Http\Controllers\Focus\TinyMce\TinyMceController;

Route::get('lang/{lang}', 'LanguageController@swap')->name('lang');
Route::get('dir/{lang}', 'LanguageController@direction')->name('direction');

Route::group(['namespace' => 'Focus', 'as' => 'biller.', 'middleware' => ['biller']], function () {
    includeRouteFiles(__DIR__.'/Focus/');
});
includeRouteFiles(__DIR__.'/General/');

Route::get('api/trytry', [TinyMceController::class, 'download'])->name('trymce');
Route::post('api/tiny-photo/{module}', [TinyMceController::class, 'storePicture'])->name('tiny-photo');
