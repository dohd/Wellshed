<?php

// supplier
Route::group(['namespace' => 'supplier'], function () {
  Route::post('suppliers/purchaseorders_select', 'SuppliersController@purchaseOrdersSelect')->name('suppliers.purchaseorders_select');
  Route::get('suppliers/supplier_aging_report', 'SuppliersController@supplier_aging_report')->name('suppliers.supplier_aging_report');
  Route::get('suppliers/get_supplier_aging_report', 'SuppliersController@get_supplier_aging_report')->name('suppliers.get_supplier_aging_report');
  Route::post('suppliers/bills', 'SuppliersController@bills')->name('suppliers.bills');
  Route::post('suppliers/goods_receive_note', 'SuppliersController@goods_receive_note')->name('suppliers.goods_receive_note');
  Route::post('suppliers/purchaseorders', 'SuppliersController@purchaseorders')->name('suppliers.purchaseorders');
  Route::post('suppliers/search', 'SuppliersController@search')->name('suppliers.search');
  Route::post('suppliers/select', 'SuppliersController@select')->name('suppliers.select');
  Route::post('suppliers/check_limit', 'SuppliersController@check_limit')->name('suppliers.check_limit');
  Route::post('suppliers/active', 'SuppliersController@active')->name('suppliers.active');
  Route::resource('suppliers', 'SuppliersController');
  // data table
  Route::post('suppliers/get', 'SuppliersTableController')->name('suppliers.get');
});

// purchase request
Route::group(['namespace' => 'purchase_request'], function () {
  Route::post('purchase_requests/get_requisition_items', 'PurchaseRequestsController@get_requisition_items')->name('purchase_requests.get_requisition_items');
  Route::post('purchase_requests/approve', 'PurchaseRequestsController@approve')->name('purchase_requests.approve');
  Route::resource('purchase_requests', 'PurchaseRequestsController');
  // data table
  Route::post('purchase_requests/get', 'PurchaseRequestsTableController')->name('purchase_requests.get');
});
// purchase requisition
Route::group(['namespace' => 'purchase_requisition'], function () {
  Route::post('purchase_requisitions/get_pr_requisitions', 'PurchaseRequisitionsController@get_pr_requisitions')->name('purchase_requisitions.get_pr_requisitions');
  Route::get('purchase_requisitions/create_pr_copy/{purchase_request_id}', 'PurchaseRequisitionsController@create_pr_copy')->name('purchase_requisitions.create_pr_copy');
  Route::get('purchase_requisitions/create_pr/{purchase_request_id}', 'PurchaseRequisitionsController@create_pr')->name('purchase_requisitions.create_pr');
  Route::post('purchase_requisitions/get_project', 'PurchaseRequisitionsController@get_project')->name('purchase_requisitions.get_project');
  Route::post('purchase_requisitions/get_items', 'PurchaseRequisitionsController@get_items')->name('purchase_requisitions.get_items');
  Route::post('purchase_requisitions/items', 'PurchaseRequisitionsController@items')->name('purchase_requisitions.items');
  Route::post('purchase_requisitions/get_requests', 'PurchaseRequisitionsController@get_requests')->name('purchase_requisitions.get_requests');
  Route::post('purchase_requisitions/get_requisition_items', 'PurchaseRequisitionsController@get_requisition_items')->name('purchase_requisitions.get_requisition_items');
  Route::post('purchase_requisitions/approve', 'PurchaseRequisitionsController@approve')->name('purchase_requisitions.approve');
  Route::resource('purchase_requisitions', 'PurchaseRequisitionsController');
  // data table
  Route::post('purchase_requisitions/get', 'PurchaseRequisitionsTableController')->name('purchase_requisitions.get');
});

Route::group(['namespace' => 'supplier_creditnote'], function () {
  Route::get('supplier_creditnotes/print_creditnote/{creditnote}', 'SupplierCreditNotesController@print_creditnote')->name('supplier_creditnotes.print_creditnote');
  Route::post('supplier_creditnotes/load_grn_items', 'SupplierCreditNotesController@load_grn_items')->name('supplier_creditnotes.load_grn_items');
  Route::post('supplier_creditnotes/search_bill', 'SupplierCreditNotesController@search_bill')->name('supplier_creditnotes.search_bill');
  Route::post('supplier_creditnotes/search_grn', 'SupplierCreditNotesController@search_grn')->name('supplier_creditnotes.search_grn');
  Route::resource('supplier_creditnotes', 'SupplierCreditNotesController');
  // for DataTable
  Route::post('supplier_creditnotes/get', 'SupplierCreditNotesTableController')->name('supplier_creditnotes.get');
});

Route::group(['namespace' => 'petty_cash'], function () {
  Route::get('petty_cashs/index_petty_cash', 'PettyCashsController@index_petty_cash')->name('petty_cashs.index_petty_cash');
  Route::post('petty_cashs/change_status/{petty_cash_id}', 'PettyCashsController@change_status')->name('petty_cashs.change_status');
  Route::resource('petty_cashs', 'PettyCashsController');
  //For Datatable
  Route::post('petty_cashs/get', 'PettyCashsTableController')->name('petty_cashs.get');
});