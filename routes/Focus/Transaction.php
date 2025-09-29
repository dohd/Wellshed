<?php

/**
 * Transactions
 */
Route::group(['namespace' => 'transaction'], function () {
  Route::get('transactions/account_search', 'TransactionsController@account_search')->name('transactions.account_search');
  Route::post('transactions/payer_search', 'TransactionsController@payer_search')->name('transactions.payer_search');
  Route::resource('transactions', 'TransactionsController');
  //For Datatable
  Route::post('transactions/get', 'TransactionsTableController')->name('transactions.get');
});
