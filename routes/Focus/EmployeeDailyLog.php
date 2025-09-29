<?php

Route::group(['namespace' => 'employeeDailyLog'], function () {
    Route::post('employee-daily-log/get_kpis', 'EmployeeDailyLogController@get_kpis')->name('employee-daily-log.get_kpis');
    Route::get('employee-daily-log/index_kpis', 'EmployeeDailyLogController@index_kpis')->name('employee-daily-log.index_kpis');
    Route::resource('employee-daily-log', 'EmployeeDailyLogController');
    Route::get('edl/{edl_number}/remark', 'EmployeeDailyLogController@makeLogRemark')->name('edl-remark');
    Route::post('edl/{edl_number}/remark/save', 'EmployeeDailyLogController@storeLogRemark')->name('edl-remark-save');
    Route::get('edl/{edl_number}/delete', 'EmployeeDailyLogController@destroy')->name('edl-delete');

//    Route::get('edl/feed', 'EmployeeDailyLogController@createPerms');


    Route::resource('employee-task-subcategories', 'EmployeeTaskSubcategoriesController');

    Route::post('employee-task-subcategories/get_data', 'EmployeeTaskSubcategoriesController@get_data')->name('employee-task-subcategories.get_data');
    Route::get('edl-subcategory-allocations', 'EdlSubcategoryAllocationController@index')->name('edl-subcategory-allocations.index');
    Route::get('edl-subcategory-allocations/{employeeId}/allocate', 'EdlSubcategoryAllocationController@create')->name('edl-subcategory-allocations.create');
    Route::get('edl-subcategory-allocations/allocate', 'EdlSubcategoryAllocationController@create')->name('edl-subcategory-allocations.allocate');
    Route::post('edl-subcategory-allocations/store', 'EdlSubcategoryAllocationController@store')->name('edl-subcategory-allocations.store');
    Route::get('edl-subcategory-allocations/{employeeId}/edit', 'EdlSubcategoryAllocationController@edit')->name('edl-subcategory-allocations.edit');
    Route::get('edl-subcategory-allocations/my-allocations', 'EdlSubcategoryAllocationController@employeeIndex')->name('edl-subcategory-allocations.allocations');

});

Route::group(['namespace' => 'health_and_safety'], function () {
    Route::resource('health-and-safety', 'HealthAndSafetyTrackingController');
    Route::get('monthly/health-and-safety/summary', 'HealthAndSafetyTrackingController@monthlySummary')->name('health-and-safety.summary');


    Route::post('projets/select', 'HealthAndSafetyTrackingController@clientProjects')->name('p.client-projects');
    //For Datatable
    Route::post('health-safety-table/get', 'HealthAndSafetyTrackingTableController')->name('health-safety-table.get');
    Route::post('health-safety-day/get', 'HealthAndSafetyTrackingController@dayIncidents')->name('day.incidents');
});

Route::group(['namespace' => 'health_and_safety_objectives'], function () {
    Route::resource('health-and-safety-objectives', 'HealthAndSafetyObjectivesController');

    // //For Datatable
    Route::post('health-safety-ojectives/get', 'HealthAndSafetyObjectivesTableController')->name('health-safety-objectives.get');
});

Route::group(['namespace' => 'health_and_safety_targets'], function () {
    Route::resource('health-and-safety-targets', 'HealthAndSafetyTargetController');

    // //For Datatable
    // Route::post('quality-tracking/get', 'QualityTrackingTableController')->name('quality-tracking.get');
});

Route::group(['namespace' => 'quality_tracking'], function () {
    Route::resource('quality-tracking', 'QualityTrackingController');

    // //For Datatable
    Route::post('quality-tracking/get', 'QualityTrackingTableController')->name('quality-tracking-table.get');
});


Route::group(['namespace' => 'environmentalTracking'], function () {
    Route::resource('environmental-tracking', 'EnvironmentalTrackingController');

    // //For Datatable
    Route::post('environmental-tracking/get', 'EnvironmentalTrackingTableController')->name('environmental-tracking-table.get');
});
Route::group(['namespace' => 'key_activity'], function () {
    Route::resource('key_activities', 'KeyActivitiesController');

    // //For Datatable
    Route::post('key_activities/get', 'KeyActivitiesTableController')->name('key_activities.get');
});