<?php

/**
 * event
 *
 */
Route::group(['namespace' => 'import'], function () {
      Route::get('sample_template/{name}', 'ImportController@sample_template')->name('import.sample_template');
      Route::post('process_template/{type?}', 'ImportController@process_template')->name('import.process_template');
      
      Route::get('import/{type?}', 'ImportController@index')->name('import.general');
      Route::post('import/{type?}', 'ImportController@store')->name('import.general');
});
