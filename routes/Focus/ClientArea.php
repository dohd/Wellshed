<?php

/**
 * Client Area
 *
 */
Route::group(['namespace' => 'tenant'], function () {
    Route::patch('tenants/update_status/{tenant}', 'TenantsController@update_status')->name('tenants.update_status');
    Route::post('tenants/customers', 'TenantsController@customers')->name('tenants.customers');
    Route::post('tenants/select', 'TenantsController@select')->name('tenants.select');
    Route::resource('tenants', 'TenantsController');
    Route::get('tenants/disable-client-accounts/{allStatusZero}/{clientBusinessId}', 'TenantsController@disableClientAccounts')->name('tenants.disable-client-accounts');
    Route::get('tenants/{id}/subscription-details', 'TenantsController@subscriptionDetails')->name('tenants.subscription-details');

    Route::post('tenants/{id}/request-grace', 'TenantsController@requestGraceDays')->name('tenants.request-grace');
    Route::post('tenants/{id}/redeem-loyalty-points', 'TenantsController@redeemLoyaltyPoints')->name('tenants.redeem-loyalty-points');

    //For Datatable
    Route::post('tenants/get', 'TenantsTableController')->name('tenants.get');
});
Route::group(['namespace' => 'tenant_service'], function () {
    Route::resource('tenant_services', 'TenantServicesController');
    //For Datatable
    Route::post('tenant_services/get', 'TenantServicesTableController')->name('tenant_services.get');
});
Route::group(['namespace' => 'tenant_invoice'], function () {
    Route::resource('tenant_invoices', 'TenantInvoicesController');
    //For Datatable
    Route::post('tenant_invoices/get', 'TenantInvoicesTableController')->name('tenant_invoices.get');
});
Route::group(['namespace' => 'tenant_deposit'], function () {
    Route::resource('tenant_deposits', 'TenantDepositsController');
    //For Datatable
    Route::post('tenant_deposits/get', 'TenantDepositsTableController')->name('tenant_deposits.get');
});
Route::group(['namespace' => 'tenant_ticket'], function () {
    Route::patch('tenant_tickets/status/{tenant_ticket}', 'TenantTicketsController@status')->name('tenant_tickets.status');
    Route::post('tenant_tickets/reply', 'TenantTicketsController@reply')->name('tenant_tickets.reply');
    Route::resource('tenant_tickets', 'TenantTicketsController');
    //For Datatable
    Route::post('tenant_tickets/get', 'TenantTicketsTableController')->name('tenant_tickets.get');
});
Route::group(['namespace' => 'ticket_category'], function () {
    Route::resource('ticket_categories', 'TicketCategoriesController');
    //For Datatable
    Route::post('ticket_categories/get', 'TicketCategoriesTableController')->name('ticket_categories.get');
});

// Mpesa Callback Urls 
Route::group(['namespace' => 'mpesa_deposit'], function () {
    Route::post('deposits/validate', 'MpesaDepositsController@validate_deposit');
    Route::post('deposits/confirm', 'MpesaDepositsController@deposit');
    Route::resource('mpesa_deposits', 'MpesaDepositsController');
    // For Datatable
    Route::post('mpesa_deposits/get', 'MpesaDepositsTableController')->name('mpesa_deposits.get');
});