<?php

Route::group(['namespace' => 'sale_return'], function () {
    Route::post('sale_returns/select_quotes', 'SaleReturnsController@select_quotes')->name('sale_returns.select_quotes');
    Route::post('sale_returns/select_invoices', 'SaleReturnsController@select_invoices')->name('sale_returns.select_invoices');
    Route::post('sale_returns/issued_stock_items', 'SaleReturnsController@issued_stock_items')->name('sale_returns.issued_stock_items');
    Route::resource('sale_returns', 'SaleReturnsController');
    // datatable
    Route::post('sale_returns/get', 'SaleReturnsTableController')->name('sale_returns.get');
});

Route::group(['namespace' => 'stock_rcv'], function () {
    Route::resource('stock_rcvs', 'StockRcvsController');
    // datatable
    Route::post('stock_rcvs/get', 'StockRcvsTableController')->name('stock_rcvs.get');
});

Route::group(['namespace' => 'stock_issue'], function () {
    Route::post('stock_issues/print_stock_movement', 'StockIssuesController@print_stock_movement')->name('stock_issues.print_stock_movement');
    Route::post('stock_issues/products_movement_items', 'StockIssuesController@products_movement_items')->name('stock_issues.products_movement_items');
    Route::post('stock_issues/quote_pi_products', 'StockIssuesController@quote_pi_products')->name('stock_issues.quote_pi_products');
    Route::post('stock_issues/select_invoices', 'StockIssuesController@select_invoices')->name('stock_issues.select_invoices');
    Route::get('stock-issues/quote-products/{quoteId}', 'StockIssuesController@quote_pi_products')->name('stock-issues.get-quote-products');
    Route::post('stock_issues/issue_invoice_items', 'StockIssuesController@issue_invoice_items')->name('stock_issues.issue_invoice_items');
    Route::get('stock_issues/get_issuance_report', 'StockIssuesController@get_issuance_report')->name('stock_issues.get_issuance_report');
    Route::resource('stock_issues', 'StockIssuesController');
    // datatable
    Route::post('stock_issues/get', 'StockIssuesTableController')->name('stock_issues.get');
    Route::get('s-issues/{id}/approval', 'StockIssuesController@updateApproval')->name('s-issues-approval');
});

Route::group(['namespace' => 'stock_adj'], function () {
    Route::post('stock_adjs/approve/{stock_adj}', 'StockAdjsController@approve_adjustment')->name('stock_adjs.approve_adjustment');
    Route::resource('stock_adjs', 'StockAdjsController');
    // datatable
    Route::post('stock_adjs/get', 'StockAdjsTableController')->name('stock_adjs.get');
});

Route::group(['namespace' => 'stock_transfer'], function () {
    Route::resource('stock_transfers', 'StockTransfersController');
    // data table
    Route::post('stock_transfers/get', 'StockTransfersTableController')->name('stock_transfers.get');
});


Route::group(['namespace' => 'goodsreceivenote'], function () {
    Route::resource('goodsreceivenote', 'GoodsReceiveNoteController');
    Route::get('grn/items-by-supplier/{supplierId}', 'GoodsReceiveNoteController@getGrnItemsBySupplier')->name('grn-items-by-supplier');
    Route::get('grn/items-by-supplier-v2', 'GoodsReceiveNoteController@getGrnItemsBySupplierV2')->name('grn-items-by-supplier-v2');
    // datatable
    Route::post('goodsreceivenote/get', 'GoodsReceiveNoteTableController')->name('goodsreceivenote.get');
});

Route::group(['namespace' => 'purchaseorder'], function () {
    Route::post('purchaseorders/update_status', 'PurchaseordersController@update_status')->name('purchaseorders.update_status');
    Route::get('purchaseorders/show_lpo_review/{review_id}', 'PurchaseordersController@show_lpo_review')->name('purchaseorders.show_lpo_review');
    Route::post('purchaseorders/get_lpo_reviews', 'PurchaseordersController@get_lpo_reviews')->name('purchaseorders.get_lpo_reviews');
    Route::get('purchaseorders/index_review', 'PurchaseordersController@index_review')->name('purchaseorders.index_review');
    Route::get('purchaseorders/create_lpo_review/{purchaseorder}', 'PurchaseordersController@create_lpo_review')->name('purchaseorders.create_lpo_review');
    Route::post('purchaseorders/lpo_review_comment/{purchaseorder}', 'PurchaseordersController@lpo_review_comment')->name('purchaseorders.lpo_review_comment');
    Route::get('purchaseorders/create_grn/{purchaseorder}', 'PurchaseordersController@create_grn')->name('purchaseorders.create_grn');
    Route::post('purchaseorders/change_status/{purchaseorder}', 'PurchaseordersController@change_status')->name('purchaseorders.change_status');
    Route::post('purchaseorders/grn/{purchaseorder}', 'PurchaseordersController@store_grn')->name('purchaseorders.grn');
    Route::post('purchaseorders/send_single_sms', 'PurchaseordersController@send_single_sms')->name('purchaseorders.send_single_sms');

    Route::post('purchaseorders/goods', 'PurchaseordersController@goods')->name('purchaseorders.goods');
    Route::resource('purchaseorders', 'PurchaseordersController');
    // data table
    Route::post('purchaseorders/get', 'PurchaseordersTableController')->name('purchaseorders.get');
});

Route::group(['namespace' => 'product'], function () {
    Route::post('products/search', 'ProductsController@search')->name('products.search');
    Route::post('products/get_categories', 'ProductsController@get_categories')->name('products.get_categories');
    Route::get('products/show_product_inventory', 'ProductsController@show_product_inventory')->name('products.show_product_inventory');
    Route::get('products/get_products', 'ProductsController@getProducts')->name('products.getProducts');
    Route::get('products/label', 'ProductsController@product_label')->name('products.product_label');
    Route::get('products/quick_add', 'ProductsController@quick_add')->name('products.quick_add');
    Route::get('products/standard', 'ProductsController@standard')->name('products.standard');
    Route::get('products/view/{id}', 'ProductsController@view')->name('products.view');
    Route::post('products/get_all_products', 'ProductsController@get_all_products')->name('products.get_all_products');
    Route::post('products/standard', 'ProductsController@standard')->name('products.standard');
    Route::post('products/label', 'ProductsController@product_label')->name('products.product_label');
    Route::get('products/stock_transfer', 'ProductsController@stock_transfer')->name('products.stock_transfer');
    Route::post('products/stock_transfer', 'ProductsController@stock_transfer')->name('products.stock_transfer');

    Route::post('products/ending_inventory', 'ProductsController@ending_inventory')->name('products.ending_inventory');
    Route::post('products/datatable_rows', 'ProductsController@datatable_rows')->name('products.datatable_rows');

    // EFRIS
    Route::get('products/efris_goods_config', 'ProductsController@efrisGoodsConfig')->name('products.efris_goods_config');
    Route::get('products/efris_goods_upload_view', 'ProductsController@efrisGoodsUploadView')->name('products.efris_goods_upload_view');

    Route::post('products/efris_goods_code_search', 'ProductsController@efrisGoodsCodeSearch')->name('products.efris_goods_code_search');
    Route::post('products/efris_goods_adjustment', 'ProductsController@efrisGoodsAdjustment')->name('products.efris_goods_adjustment');
    Route::post('products/efris_goods_adj_modal', 'ProductsController@efrisGoodsAdjModal')->name('products.efris_goods_adj_modal');
    Route::post('products/efris_goods_config_productvar_data', 'ProductsController@efrisGoodsConfigProductVarData')->name('products.efris_goods_config_productvar_data');
    Route::post('products/efris_goods_config_data', 'ProductsController@efrisGoodsConfigData')->name('products.efris_goods_config_data');
    Route::post('products/efris_assign_commodity_code', 'ProductsController@efrisAssignCommodityCode')->name('products.efris_assign_commodity_code');
    Route::post('products/efris_goods_modal_data', 'ProductsController@efrisGoodsModalData')->name('products.efris_goods_modal_data');
    Route::post('products/efris_goods_upload', 'ProductsController@efrisGoodsUpload')->name('products.efris_goods_upload');

    //For Datatable
    Route::post('products/get', 'ProductsTableController')->name('products.get');
    Route::post('products/search/{bill_type}', 'ProductsController@product_search')->name('products.product_search');
    Route::post('products/quote', 'ProductsController@quote_product_search')->name('products.quote_product_search');
    Route::post('products/purchase_search', 'ProductsController@purchase_search')->name('products.purchase_search');

    Route::post('products/product_sub_load', 'ProductsController@product_sub_load')->name('products.product_sub_load');
    Route::post('products/pos/{bill_type}', 'ProductsController@pos')->name('products.product_search');
    Route::resource('products', 'ProductsController');

    Route::get('fix-products', 'ProductsController@clearNegativeQuantities');
});

Route::group(['namespace' => 'part'], function () {
    Route::post('parts/get_items', 'PartsController@get_items')->name('parts.get_items');
    Route::post('parts/add_finished_product/{part_id}', 'PartsController@add_finished_product')->name('parts.add_finished_product');
    Route::resource('parts', 'PartsController');
    // data table
    Route::post('parts/get', 'PartsTableController')->name('parts.get');
});
Route::group(['namespace' => 'standard_template'], function () {
    Route::post('standard_templates/get_std_templates', 'StandardTemplatesController@get_std_templates')->name('standard_templates.get_std_templates');
    Route::resource('standard_templates', 'StandardTemplatesController');
    // data table
    Route::post('standard_templates/get', 'StandardTemplatesTableController')->name('standard_templates.get');
});
