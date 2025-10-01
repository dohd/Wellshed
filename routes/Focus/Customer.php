<?php

/**
 * customers
 *
 */
Route::group(['namespace' => 'customer'], function () {
    // EFRIS
    Route::post('efris/customer_tin_info', 'CustomersController@queryTaxPayerInfo')->name('customers.query_tin_info');

    Route::get('customers/print_statement/{customer_id}/{token}/{type}', 'CustomersController@print_statement')->name('customers.print_statement');

    Route::post('customer_send_email', 'CustomersController@send_bill')->name('customer_send_email');
    Route::post('customers/selected', 'CustomersController@selected_action')->name('customers.selected_action');
    Route::get('customers/wallet', 'CustomersController@wallet')->name('customers.wallet');
    Route::post('customers/send_statement', 'CustomersController@send_statement')->name('customers.send_statement');
    Route::get('customers/aging_report', 'CustomersController@aging_report')->name('customers.aging_report');
    Route::get('customers/get_aging_report', 'CustomersController@get_aging_report')->name('customers.get_aging_report');
    Route::post('customers/wallet', 'CustomersController@wallet')->name('customers.wallet');
    Route::post('customers/wallet_load', 'CustomersController@wallet_transactions')->name('customers.wallet_load');
    Route::post('customers/search', 'CustomersController@search')->name('customers.search');
    Route::post('customers/select', 'CustomersController@select')->name('customers.select');
    Route::post('customers/check_limit', 'CustomersController@check_limit')->name('customers.check_limit');
    Route::post('customers/active', 'CustomersController@select')->name('customers.active');
    Route::resource('customers', 'CustomersController');
    //For Datatable
    Route::post('customers/get', 'CustomersTableController')->name('customers.get');
});

Route::group(['namespace' => 'customer_complain'], function () {
    Route::resource('customer_complains', 'CustomerComplainsController');
    //For Datatable
    Route::post('customer_complains/get', 'CustomerComplainsTableController')->name('customer_complains.get');
});
Route::group(['namespace' => 'customer_enrollment'], function () {
    Route::post('customer_enrollments/notify_referrers/{customer_enrollment_id}', 'CustomerEnrollmentsController@notify_referrers')->name('customer_enrollments.notify_referrers');
    Route::post('customer_enrollments/change_payment_status', 'CustomerEnrollmentsController@change_payment_status')->name('customer_enrollments.change_payment_status');
    Route::post('customer_enrollments/change_status/{customer_enrollment_id}', 'CustomerEnrollmentsController@change_status')->name('customer_enrollments.change_status');
    Route::post('customer_enrollments/get_redeemable_codes', 'CustomerEnrollmentsController@get_redeemable_codes')->name('customer_enrollments.get_redeemable_codes');
    Route::resource('customer_enrollments', 'CustomerEnrollmentsController');
    //For Datatable
    Route::post('customer_enrollments/get', 'CustomerEnrollmentsTableController')->name('customer_enrollments.get');
});

Route::group(['namespace' => 'orders'], function () {
    Route::resource('customer_orders', 'OrdersController');
    //For Datatable
    Route::post('customer_orders/get', 'OrdersTableController')->name('customer_orders.get');
});
