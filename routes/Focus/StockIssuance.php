<?php
Route::group(['namespace' => 'stockIssuance'], function () {


    Route::resource('stock-issuance-request', 'StockIssuanceRequestController');

    Route::get('/sir-table', 'StockIssuanceRequestController@getSirDataTable')->name('sir-table');


    Route::resource('stock-issuance-approval', 'StockIssuanceApprovalController');
    Route::get('/sir-approve/{sirNumber}', 'StockIssuanceApprovalController@approve')->name('sir-approve');
    Route::get('/sir-reject/{siaNumber}', 'StockIssuanceApprovalController@reject')->name('sir-reject');


});

