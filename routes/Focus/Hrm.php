<?php

Route::group(['namespace' => 'advance_payment'], function () {
    Route::resource('advance_payments', 'AdvancePaymentController');
    // data table
    Route::post('advance_payments/get', 'AdvancePaymentTableController')->name('advance_payments.get');
});

Route::group(['namespace' => 'attendance'], function () {
    Route::post('attendances/update_status/{attendance}', 'AttendanceController@update_status')->name('attendances.update_status');
    Route::post('attendances/employees_attendance', 'AttendanceController@employees_attendance')->name('attendances.employees_attendance');
    Route::post('attendances/day_attendance', 'AttendanceController@day_attendance')->name('attendances.day_attendance');
    Route::resource('attendances', 'AttendanceController');
    // data table
    Route::post('attendances/get', 'AttendanceTableController')->name('attendances.get');
});

Route::group(['namespace' => 'leave'], function () {
    Route::post('leave/get_end_date', 'LeaveController@get_end_date')->name('leave.get_end_date');
    Route::post('leave/approve/{leave}', 'LeaveController@approve')->name('leave.approve');
    Route::post('leave/leave_categories', 'LeaveController@leave_categories')->name('leave.leave_categories');
    Route::resource('leave', 'LeaveController');
    // data table
    Route::post('leave/get', 'LeaveTableController')->name('leave.get');
});

Route::group(['namespace' => 'leave_category'], function () {
    Route::resource('leave_category', 'LeaveCategoryController');
    // data table
    Route::post('leave_category/get', 'LeaveCategoryTableController')->name('leave_category.get');
});

Route::group(['namespace' => 'holiday_list'], function () {
    Route::resource('holiday_list', 'HolidayListController');
    // data table
    Route::post('holiday_list/get', 'HolidayListTableController')->name('holiday_list.get');
});

Route::group(['namespace' => 'wage_item'], function () {
    Route::resource('wage_items', 'WageItemsController');
    // data table
    Route::post('wage_items/get', 'WageItemsTableController')->name('wage_items.get');
});


Route::group(['namespace' => 'hrm'], function () {
    Route::post('hrms/set_permission', 'HrmsController@set_permission')->name('hrms.set_permission');
    Route::get('hrms/payroll', 'HrmsController@payroll')->name('hrms.payroll');
    Route::get('hrms/attendance_new', 'HrmsController@attendance')->name('hrms.attendance');
    Route::get('hrms/attendance_destroy', 'HrmsController@attendance_destroy')->name('hrms.attendance_destroy');
    Route::post('hrms/attendance_destroy', 'HrmsController@attendance_destroy')->name('hrms.attendance_destroy');
    Route::get('hrms/attendance', 'HrmsController@attendance_list')->name('hrms.attendance_list');
    Route::post('hrms/attendance', 'HrmsController@attendance_store')->name('hrms.attendance_store');
    Route::get('hrms/payroll', 'HrmsController@payroll')->name('hrms.payroll');

    Route::post('hrms/related_permission', 'HrmsController@related_permission')->name('hrms.related_permission');
    Route::post('hrms/role_permission', 'HrmsController@role_permission')->name('hrms.role_permission');
    Route::post('hrms/active', 'HrmsController@active')->name('hrms.active');
    Route::post('hrms/admin_permissions', 'HrmsController@admin_permissions')->name('hrms.admin_role_permission');

    Route::resource('hrms', 'HrmsController');
    //For Datatable
    Route::post('hrms/get', 'HrmsTableController')->name('hrms.get');
    Route::post('hrms/get_attendance', 'HrmAttendanceTableController')->name('hrms.get_attendance');
});


Route::group(['namespace' => 'role'], function () {
    Route::resource('role', 'RoleController', ['except' => ['show']]);

    //For DataTables
    Route::post('role/get', 'RoleTableController')->name('role.get');
    Route::get('role/{id}/trash', 'RoleController@destroy')->name('trash-role');
});

Route::group(['namespace' => 'salary'], function () {
    Route::post('salary/renew_contract', 'SalaryController@renew_contract')->name('salary.renew_contract');
    Route::post('salary/terminate_contract', 'SalaryController@terminate_contract')->name('salary.terminate_contract');
    Route::post('salary/select', 'SalaryController@select')->name('salary.select');
    Route::resource('salary', 'SalaryController');
    //For Datatable
    Route::post('salary/get', 'SalaryTableController')->name('salary.get');
});




Route::group(['namespace' => 'payroll'], function () {

    Route::resource('payroll', 'PayrollController');
    Route::get('/payroll-delete/{id}', 'PayrollController@deletePayroll')->name('payroll-delete');
    //For Datatable
    Route::post('payroll/get', 'PayrollTableController')->name('payroll.get');
    Route::get('payrollTable/data', 'PayrollTableController@payrollData')->name('payroll.get-data');
    Route::post('get_employee', 'PayrollController@get_employee')->name('payroll.get_employee');

    Route::group(['prefix' => 'payroll'], function () {

        Route::get('/processing/{id}', 'PayrollController@processPayroll')->name('payroll.page');
        Route::post('/store_basic', 'PayrollController@store_basic')->name('payroll.store_basic');
        Route::post('/store_allowance', 'PayrollController@store_allowance')->name('payroll.store_allowance');
        Route::post('/store_deduction', 'PayrollController@store_deduction')->name('payroll.store_deduction');
        Route::post('/store_otherdeduction', 'PayrollController@store_otherdeduction')->name('payroll.store_otherdeduction');
        Route::post('/store_summary', 'PayrollController@store_summary')->name('payroll.store_summary');
        Route::post('/store_paye', 'PayrollController@store_paye')->name('payroll.store_paye');
        Route::post('/store_nhif', 'PayrollController@store_nhif')->name('payroll.store_nhif');
        Route::post('/approve_payroll', 'PayrollController@approve_payroll')->name('payroll.approve_payroll');
        Route::post('/send_mail', 'PayrollController@send_mail')->name('payroll.send_mail');
        Route::post('/update_basic', 'PayrollController@update_basic')->name('payroll.update_basic');
        Route::post('/update_allowance', 'PayrollController@update_allowance')->name('payroll.update_allowance');
        Route::post('/update_deduction', 'PayrollController@update_deduction')->name('payroll.update_deduction');
        Route::post('/update_other', 'PayrollController@update_other')->name('payroll.update_other');
        Route::get('/reports/{id}', 'PayrollController@reports')->name('payroll.reports');
        Route::post('/get-reports/{payrollID}', 'PayrollController@get_reports')->name('payroll.get_reports');

        Route::post('/get-reports/nssf/{payrollID}', 'PayrollController@getNssfReport')->name('payroll.getNssfReport');
        Route::post('/get-reports/nhif/{payrollID}', 'PayrollController@getNhifReport')->name('payroll.getNhifReport');
        Route::post('/get-reports/paye/{payrollID}', 'PayrollController@getPayeReport')->name('payroll.getPayeReport');
        Route::post('/get-reports/hl/{payrollID}', 'PayrollController@getHousingLevyReport')->name('payroll.getHousingLevyReport');
        Route::post('/get-reports/erp-commissions/{payrollID}', 'PayrollController@getErpCommissionsReport')->name('payroll.getErpCommissionsReport');

    });
});

Route::group(['namespace' => 'casual'], function () {
    Route::resource('casuals', 'CasualsController');
    //For Datatable
    Route::post('casuals/get', 'CasualsTableController')->name('casuals.get');
});

Route::group(['namespace' => 'sale_agent'], function () {
    Route::post('sale_agent/get', 'SaleAgentsController@get')->name('sale_agents.get');
    Route::get('sale_agent/index', 'SaleAgentsController@index')->name('sale_agents.index');
});

