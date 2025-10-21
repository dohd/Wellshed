<?php

Route::group( ['namespace' => 'subpackage'], function () {
  Route::resource('subpackages', 'SubPackagesController');
  //For Datatable
  Route::post('subpackages/get', 'SubPackagesTableController')->name('subpackages.get');
});

Route::group( ['namespace' => 'subscription'], function () {
  Route::resource('subscriptions', 'SubscriptionsController');
  //For Datatable
  Route::post('subscriptions/get', 'SubscriptionsTableController')->name('subscriptions.get');
});
