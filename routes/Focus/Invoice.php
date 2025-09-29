<?php

/**
 * invoices
 *
 */

//  Electronic Tax Register
Route::group(['namespace' => 'etr'], function () {
    // Efris
    Route::post('efris/query_invoices', 'EfrisController@queryInvoices')->name('efris.query_invoices');
    Route::post('efris/invoice_upload', 'EfrisController@invoiceUpload')->name('efris.invoice_upload');
    Route::post('efris/stock_maintain', 'EfrisController@stockMaintain')->name('efris.stock_maintain');
    Route::post('efris/query_goods', 'EfrisController@queryGoods')->name('efris.query_goods');
    Route::post('efris/system_dictionary_update', 'EfrisController@systemDictionaryUpdate')->name('efris.system_dictionary_update');
    Route::post('efris/goods_upload', 'EfrisController@goodsUpload')->name('efris.goods_upload');
    Route::post('efris/tax_payer_info', 'EfrisController@infoByTinOrNinBrn')->name('efris.tax_payer_info');
    Route::post('efris/get_all_exchange_rates', 'EfrisController@getAllExchangeRates')->name('efris.get_all_exchange_rates');
    Route::post('efris/get_symmetric_key', 'EfrisController@getSymmetricKey')->name('efris.get_symmetric_key');
    Route::post('efris/get_server_time', 'EfrisController@getServerTime')->name('efris.get_server_time');

    // DigiTax
    Route::post('digitax/validation', 'DigitaxController@validation')->name('digitax.validation');
});
 
Route::group(['namespace' => 'creditnote'], function () {
    // Efris
    Route::post('efris/query_creditnotes', 'CreditNotesController@efrisQueryCreditNoteStatus')->name('creditnotes.efris_query');
    Route::post('efris_validate_creditnote', 'CreditNotesController@efrisCreditNoteUpload')->name('creditnotes.efris_validate');

    Route::get('creditnotes/print_creditnote/{creditnote}', 'CreditNotesController@print_creditnote')->name('creditnotes.print_creditnote');
    Route::post('creditnotes/load_invoice_items', 'CreditNotesController@load_invoice_items')->name('creditnotes.load_invoice_items');
    Route::post('creditnotes/search_invoice', 'CreditNotesController@search_invoice')->name('creditnotes.search_invoice');
    Route::resource('creditnotes', 'CreditNotesController');
    // for DataTable
    Route::post('creditnotes/get', 'CreditNotesTableController')->name('creditnotes.get');
});


Route::group(['namespace' => 'job_valuation'], function () {
    Route::post('job_valuations/service_expense', 'JobValuationsController@serviceExpense')->name('job_valuations.service_expense');
    Route::post('job_valuations/material_expense', 'JobValuationsController@materialExpense')->name('job_valuations.material_expense');
    Route::get('job_valuations/quote_index', 'JobValuationsController@quote_index')->name('job_valuations.quote_index');
    Route::resource('job_valuations', 'JobValuationsController');
    // datatable
    Route::post('job_valuations/quotes', 'JobValuationsController@quotesDatatable')->name('job_valuations.get_quotes');
    Route::post('job_valuations/get', 'JobValuationsTableController')->name('job_valuations.get');
});
Route::group(['namespace' => 'boq_valuation'], function () {
    Route::post('boq_valuations/service_expense', 'BoQValuationsController@serviceExpense')->name('boq_valuations.service_expense');
    Route::post('boq_valuations/material_expense', 'BoQValuationsController@materialExpense')->name('boq_valuations.material_expense');
    Route::get('boq_valuations/boq_index', 'BoQValuationsController@boq_index')->name('boq_valuations.boq_index');
    Route::resource('boq_valuations', 'BoQValuationsController');
    // datatable
    Route::post('boq_valuations/boqs', 'BoQValuationsController@boqsDatatable')->name('boq_valuations.get_boqs');
    Route::post('boq_valuations/get', 'BoQValuationsTableController')->name('boq_valuations.get');
});

Route::group(['namespace' => 'estimate'], function () {
    Route::post('estimates/verified_products', 'EstimatesController@verified_products')->name('estimates.verified_products');
    Route::post('estimates/quote_select', 'EstimatesController@quote_select')->name('estimates.quote_select');
    Route::resource('estimates', 'EstimatesController');
    // datatable
    Route::post('estimates/get', 'EstimatesTableController')->name('estimates.get');
});

Route::prefix('cu')->namespace('cuInvoiceNumber')->group(function () {
    Route::get('set', 'CuInvoiceNumberController@set');
    Route::resource('control-unit-invoice-number', 'ControlUnitInvoiceNumberController');
    Route::get('check-control-unit-invoice-number', 'ControlUnitInvoiceNumberController@checkCuInvoiceNumber')->name('check-control-unit-invoice-number');
    Route::get('set-control-unit-invoice-number', 'ControlUnitInvoiceNumberController@setCuInvoiceNumber')->name('set-control-unit-invoice-number');
});

Route::group(['namespace' => 'standard_invoice'], function () {
    Route::post('standard_invoices/customer/create', 'StandardInvoicesController@create_customer')->name('invoices.create_customer');
    Route::resource('standard_invoices', 'StandardInvoicesController');
});

// payment
Route::group(['namespace' => 'invoice_payment'], function () {
    Route::post('invoice_payments/send_sms_and_email/{invoice_payment}', 'InvoicePaymentsController@send_sms_and_email')->name('invoice_payments.send_sms_and_email');
    Route::post('invoice_payments/select_unallocated_payments', 'InvoicePaymentsController@select_unallocated_payments')->name('invoice_payments.select_unallocated_payments');
    Route::resource('invoice_payments', 'InvoicePaymentsController');
    // datatable
    Route::post('invoice_payments/get_payments', 'InvoicePaymentsTableController')->name('invoice_payments.get');
});

Route::group(['namespace' => 'invoice'], function () {
    // Validate
    Route::post('efris/query_invoice', 'InvoicesController@queryInvoice')->name('invoices.query_invoice');
    Route::post('efris_validate_invoice', 'InvoicesController@efrisInvoiceUpload')->name('invoices.efris_validate');

    Route::post('bill_status', 'InvoicesController@update_status')->name('bill_status');
    Route::get('pos', 'InvoicesController@pos')->name('invoices.pos');
    Route::post('pos_create', 'InvoicesController@pos_store')->name('invoices.pos_store');
    Route::post('draft_store', 'InvoicesController@draft_store')->name('invoices.draft_store');
    Route::post('drafts_load', 'InvoicesController@drafts_load')->name('invoices.drafts_load');
    Route::get('draft_view/{id}', 'InvoicesController@draft_view')->name('invoices.draft_view');
    Route::post('pos_update', 'InvoicesController@pos_update')->name('invoices.pos_update');
    Route::get('invoices/client_invoices', 'InvoicesController@client_invoices')->name('invoices.client_invoices');
    Route::post('invoices/nullify_invoice/{invoice}', 'InvoicesController@nullify_invoice')->name('invoices.nullify_invoice');
    Route::post('invoices/unallocated_payment', 'InvoicesController@unallocated_payment')->name('invoices.unallocated_payment');

    // payment
    Route::post('invoices/send_sms_and_email/{invoice_id}', 'InvoicesController@send_sms_and_email')->name('invoices.send_sms_and_email');
    Route::get('invoices/print_payment/{paidinvoice}', 'InvoicesController@print_payment')->name('invoices.print_payment');
    Route::get('invoices/index_payment', 'InvoicesController@index_payment')->name('invoices.index_payment');
    Route::get('invoices/create_payment', 'InvoicesController@create_payment')->name('invoices.create_payment');
    Route::post('invoices/store_payment', 'InvoicesController@store_payment')->name('invoices.store_payment');
    Route::get('invoices/edit_payment/{payment}', 'InvoicesController@edit_payment')->name('invoices.edit_payment');
    Route::get('invoices/show_payment/{payment}', 'InvoicesController@show_payment')->name('invoices.show_payment');
    Route::patch('invoices/update_payment/{payment}', 'InvoicesController@update_payment')->name('invoices.update_payment');
    Route::post('invoices/delete_payment/{payment}', 'InvoicesController@delete_payment')->name('invoices.delete_payment');

    // project invoice
    Route::get('invoices/edit_project_invoice/{invoice}', 'InvoicesController@edit_project_invoice')->name('invoices.edit_project_invoice');
    Route::post('invoices/update_project_invoice/{invoice}', 'InvoicesController@update_project_invoice')->name('invoices.update_project_invoice');
    Route::get('filter_invoice_quotes', 'InvoicesController@filter_invoice_quotes')->name('invoices.filter_invoice_quotes');
    Route::post('store_project_invoice', 'InvoicesController@store_project_invoice')->name('invoices.store_project_invoice');
    Route::get('invoices/print_document/{id}/{type}', 'InvoicesController@print_document')->name('invoices.print_document');

    Route::get('ipc_retention', 'InvoicesController@ipcRetention')->name('invoices.ipc_retention');
    Route::get('sales_variance', 'InvoicesController@salesVariance')->name('invoices.sales_variance');
    Route::get('uninvoiced_quote', 'InvoicesController@uninvoiced_quote')->name('invoices.uninvoiced_quote');
    Route::resource('invoices', 'InvoicesController');
    //For Datatable
    Route::post('ipc_retention/get', 'IpcRetentionsTableController')->name('ipc_retention.get');
    Route::post('sales_variance/get', 'SalesVarianceTableController')->name('sales_variance.get');
    Route::post('quotes/get_uninvoiced_quote', 'UninvoicedQuoteTableController')->name('invoices.get_uninvoiced_quote');
    Route::post('invoices/get', 'InvoicesTableController')->name('invoices.get');
    Route::post('invoices/get_payments', 'InvoicePaymentsTableController')->name('invoices.get_payments');
});

Route::group(['namespace' => 'printer'], function () {
    Route::get('browser_print', 'PrinterController@browser_print')->name('pos.browser_print');
    Route::post('register/open', 'RegistersController@open')->name('register.open');
    Route::get('register/close', 'RegistersController@close')->name('register.close');
    Route::get('register/load', 'RegistersController@load')->name('register.load');
});
