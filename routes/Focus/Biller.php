<?php

use App\Http\Controllers\Focus\dailyBusinessMetrics\DailyBusinessMetricController;
use App\Http\Controllers\Focus\documentBoard\DocumentBoardController;
use App\Http\Controllers\Focus\employeeNotice\EmployeeNoticeController;
use App\Http\Controllers\Focus\marquee\MarqueeController;
use App\Http\Controllers\Focus\promotions\ClientFeedbackController;
use App\Http\Controllers\Focus\promotions\CompanyPromotionalPrefixController;
use App\Http\Controllers\Focus\promotions\PromoCodeReservationController;
use App\Http\Controllers\Focus\promotions\PromotionalCodeController;
use App\Http\Controllers\Focus\PurchaseClass\PurchaseClassController;
use App\Http\Controllers\Focus\recentCustomer\RecentCustomerController;

Route::group(['namespace' => 'refill_customer'], function () {
    Route::resource('refill_customers', 'RefillCustomersController');
    Route::post('refill_customers/get', 'RefillCustomersTableController')->name('refill_customers.get');
});
Route::group(['namespace' => 'refill_product'], function () {
    Route::resource('refill_products', 'RefillProductsController');
    Route::post('refill_products/get', 'RefillProductsTableController')->name('refill_products.get');
});
Route::group(['namespace' => 'refill_product_category'], function () {
    Route::resource('refill_product_categories', 'RefillProductCategoriesController');
    Route::post('refills/product_categories/get', 'RefillProductCategoriesTableController')->name('refill_product_categories.get');
});
Route::group(['namespace' => 'product_refill'], function () {
    Route::resource('product_refills', 'ProductRefillsController');
    Route::post('product_refills/get', 'ProductRefillsTableController')->name('product_refills.get');
});

// Utility bills
Route::group(['namespace' => 'utility_bill'], function () {
    Route::get('utility_bills/create-kra', 'UtilityBillsController@create_kra_bill')->name('utility_bills.create_kra_bill');

    Route::post('utility_bills/employee_bills', 'UtilityBillsController@employee_bills')->name('utility_bills.employee_bills');
    Route::post('utility_bills/grn', 'UtilityBillsController@goods_receive_note')->name('utility_bills.goods_receive_note');
    Route::post('utility_bills/store_kra_bill', 'UtilityBillsController@store_kra_bill')->name('utility_bills.store_kra_bill');
    Route::resource('utility_bills', 'UtilityBillsController');
    // data table
    Route::post('utility_bills/get', 'UtilityBillsTableController')->name('utility_bills.get');
});

// supplier bill payment
Route::group(['namespace' => 'billpayment'], function () {
    Route::resource('billpayments', 'BillPaymentController');
    // data table
    Route::post('billpayments/get', 'BillPaymentTableController')->name('billpayments.get');
  });
  

//  Accounts
Route::group(['namespace' => 'account'], function () {
    Route::get('accounts/cash_flow_statement/{type}', 'AccountsController@cash_flow_statement')->name('accounts.cash_flow_statement');
    Route::get('accounts/profit_and_loss/{type}', 'AccountsController@profit_and_loss')->name('accounts.profit_and_loss');
    Route::get('accounts/balancesheet/{type}', 'AccountsController@balance_sheet')->name('accounts.balance_sheet');
    Route::get('accounts/trialbalance/{type}', 'AccountsController@trial_balance')->name('accounts.trial_balance');
    Route::get('accounts/project_gross_profit', 'AccountsController@project_gross_profit')->name('accounts.project_gross_profit');
    Route::get('accounts/cashbook', 'AccountsController@cashbook')->name('accounts.cashbook');
    Route::get('accounts/general_ledger', 'AccountsController@generalLedger')->name('accounts.general_ledger');

    Route::post('accounts/journal_entries', 'AccountsController@journalEntries')->name('accounts.journal_entries');
    Route::post('accounts/search_parent_account', 'AccountsController@search_parent_account')->name('accounts.search_parent_account');
    Route::post('accounts/search_detail_type', 'AccountsController@search_detail_type')->name('accounts.search_detail_type');
    Route::post('accounts/search_next_account_no', 'AccountsController@search_next_account_no')->name('accounts.search_next_account_no');
    Route::post('accounts/search', 'AccountsController@account_search')->name('accounts.account_search');
    Route::resource('accounts', 'AccountsController');
    //For Datatable
    Route::post('accounts/cashbook/transactions', 'CashbookTableController')->name('accounts.get_cashbook');
    Route::post('accounts/project_gross_profit/get', 'ProjectGrossProfitTableController')->name('accounts.get_project_gross_profit');
    Route::post('accounts/get', 'AccountsTableController')->name('accounts.get');
    Route::get('get-gp-chart-data', 'ProjectGrossProfitTableController@getGpChartData')->name('get-gp-chart-data');

});
// Tax Return
Route::group(['namespace' => 'tax_report'], function () {
    Route::get('tax_reports/filed_report', 'TaxReportsController@filed_report')->name('tax_reports.filed_report');
    Route::post('tax_reports/purchases', 'TaxReportsController@get_purchases')->name('tax_reports.get_purchases');
    Route::post('tax_reports/sales', 'TaxReportsController@get_sales')->name('tax_reports.get_sales');
    Route::resource('tax_reports', 'TaxReportsController');
    // data table
    Route::post('tax_reports/get_filed_items', 'FiledTaxReportsTableController')->name('tax_reports.get_filed_items');
    Route::get('/export-to-excel', 'FiledTaxReportsTableController@exportToExcel');
    Route::post('tax_reports/get', 'TaxReportsTableController')->name('tax_reports.get');
});
Route::group(['namespace' => 'tax_prn'], function () {
    Route::resource('tax_prns', 'TaxPrnsController');
    // data table
    Route::post('tax_prns/get', 'TaxPrnsTableController')->name('tax_prns.get');
});
Route::group(['namespace' => 'allowance'], function () {
    Route::resource('allowances', 'AllowancesController');
    //For Datatable
    Route::post('allowances/get', 'AllowancesTableController')->name('allowances.get');
});

Route::group(['namespace' => 'additional'], function () {
    Route::resource('additionals', 'AdditionalsController');
    //For Datatable
    Route::post('additionals/get', 'AdditionalsTableController')->name('additionals.get');
});

Route::group(['namespace' => 'assetequipment'], function () {
    Route::resource('assetequipments', 'AssetequipmentsController');
    Route::post('assetequipments/ledger_load', 'AssetequipmentsController@ledger_load')->name('assetequipments.ledger_load');
    Route::post('assetequipments/search', 'AssetequipmentsController@product_search')->name('assetequipments.product_search');
    //For Datatable
    Route::post('assetequipments/get', 'AssetequipmentsTableController')->name('assetequipments.get');
});

Route::group(['namespace' => 'toolkit'], function () {
    Route::post('toolkits/select', 'ToolkitController@select')->name('toolkits.select');
    Route::post('toolkits/load', 'ToolkitController@load')->name('toolkits.load');
    Route::resource('toolkits', 'ToolkitController');

    //For Datatable
    Route::post('toolkits/get', 'ToolkitTableController')->name('toolkits.get');
});

Route::group(['namespace' => 'workshift'], function () {
    Route::post('workshifts/select', 'WorkshiftController@select')->name('workshifts.select');
    Route::post('workshifts/load', 'WorkshiftController@load')->name('workshifts.load');
    Route::resource('workshifts', 'WorkshiftController');

    //For Datatable
    Route::post('workshifts/get', 'WorkshiftTableController')->name('workshifts.get');
});

Route::group(['namespace' => 'bank_feed'], function () {
    Route::resource('bank_feeds', 'BankFeedsController');
    //For Datatable
    Route::post('bank_feeds/get', 'BankFeedsTableController')->name('bank_feeds.get');
});

Route::group(['namespace' => 'bank'], function () {
    Route::resource('banks', 'BanksController');
    //For Datatable
    Route::post('banks/get', 'BanksTableController')->name('banks.get');
});
Route::group(['namespace' => 'banktransfer'], function () {
    Route::resource('banktransfers', 'BanktransfersController');
    //For Datatable
    Route::post('banktransfers/get', 'BanktransfersTableController')->name('banktransfers.get');
});
Route::group(['namespace' => 'branch'], function () {
    Route::post('branches/select', 'BranchesController@select')->name('branches.select');
    Route::resource('branches', 'BranchesController');
    //For Datatable
    Route::post('branches/get', 'BranchesTableController')->name('branches.get');
});
Route::group(['namespace' => 'charge'], function () {
    Route::resource('charges', 'ChargesController');
    //For Datatable
    Route::post('charges/get', 'ChargesTableController')->name('charges.get');
});

Route::group(['namespace' => 'creditor'], function () {
    Route::resource('creditors', 'CreditorsController');
    //For Datatable
    Route::post('creditors/get', 'CreditorsTableController')->name('creditors.get');
});
Route::group(['namespace' => 'currency'], function () {
    Route::post('currencies/load', 'CurrenciesController@load')->name('currencies.load');
    Route::resource('currencies', 'CurrenciesController');
    //For Datatable
    Route::post('currencies/get', 'CurrenciesTableController')->name('currencies.get');
});
Route::group(['namespace' => 'customergroup'], function () {
    Route::resource('customergroups', 'CustomergroupsController');
    //For Datatable
    Route::post('customergroups/get', 'CustomergroupsTableController')->name('customergroups.get');
});

Route::group(['namespace' => 'customfield'], function () {
    Route::resource('customfields', 'CustomfieldsController');
    //For Datatable
    Route::post('customfields/get', 'CustomfieldsTableController')->name('customfields.get');
});
Route::group(['namespace' => 'deptor'], function () {
    Route::resource('deptors', 'DebtorsController');
    //For Datatable
    //Route::post('deptors/get', 'DebtorsTableController')->name('deptors.get');
});
Route::group(['namespace' => 'department'], function () {
    Route::resource('departments', 'DepartmentsController');
    //For Datatable
    Route::post('departments/get', 'DepartmentsTableController')->name('departments.get');
});
Route::group(['namespace' => 'customer_page'], function () {
    Route::get('customer_pages/subscriptions', 'CustomerPagesController@subscriptions')->name('customer_pages.subscriptions');
    Route::get('customer_pages/my_orders', 'CustomerPagesController@my_orders')->name('customer_pages.my_orders');
    Route::get('customer_pages/payments', 'CustomerPagesController@payments')->name('customer_pages.payments');
    Route::post('customer_pages/submit_order', 'CustomerPagesController@submit_order')->name('customer_pages.submit_order');
    Route::get('customer_pages/home', 'CustomerPagesController@home')->name('customer_pages.home');
    Route::get('customer_pages/orders', 'CustomerPagesController@orders')->name('customer_pages.orders');
    Route::get('customer_pages/track', 'CustomerPagesController@track')->name('customer_pages.track');
    Route::get('customer_pages/profile', 'CustomerPagesController@profile')->name('customer_pages.profile');
    Route::get('customer_pages/delivery', 'CustomerPagesController@delivery')->name('customer_pages.delivery');
    Route::get('customer_pages/review', 'CustomerPagesController@review')->name('customer_pages.review');
    Route::get('customer_pages/thank_you', 'CustomerPagesController@thank_you')->name('customer_pages.thank_you');
});

Route::group(['namespace' => 'mpesa_config'], function () {
    Route::post('mpesa_configs/get', 'MpesaConfigsController@get')->name('mpesa_configs.get');
    Route::resource('mpesa_configs', 'MpesaConfigsController');
    //For Datatable
});

Route::group(['namespace' => 'target_zone'], function () {
    Route::resource('target_zones', 'TargetZonesController');
    //For Datatable
    Route::post('target_zones/get', 'TargetZonesTableController')->name('target_zones.get');
});
Route::group(['namespace' => 'delivery'], function () {
    Route::post('deliveries/change_status', 'DeliveriesController@change_status')->name('deliveries.change_status');
    Route::resource('deliveries', 'DeliveriesController');
    //For Datatable
    Route::post('deliveries/get', 'DeliveriesTableController')->name('deliveries.get');
});
Route::group(['namespace' => 'delivery_schedule'], function () {
    Route::post('delivery_schedules/product_movement_pdf', 'DeliverySchedulesController@product_movement_pdf')->name('delivery_schedules.product_movement_pdf');
    Route::post('delivery_schedules/exportPdf', 'DeliverySchedulesController@exportPdf')->name('delivery_schedules.exportPdf');
    Route::get('delivery_schedules/product_movement_report', 'DeliverySchedulesController@product_movement_report')->name('delivery_schedules.product_movement_report');
    Route::get('delivery_schedules/daily_delivery_report', 'DeliverySchedulesController@daily_delivery_report')->name('delivery_schedules.daily_delivery_report');
    Route::post('delivery_schedules/update_status', 'DeliverySchedulesController@update_status')->name('delivery_schedules.update_status');
    Route::post('delivery_schedules/get_schedule_items', 'DeliverySchedulesController@get_schedule_items')->name('delivery_schedules.get_schedule_items');
    Route::post('delivery_schedules/get_schedules', 'DeliverySchedulesController@get_schedules')->name('delivery_schedules.get_schedules');
    Route::resource('delivery_schedules', 'DeliverySchedulesController');
    //For Datatable
    Route::post('delivery_schedules/get', 'DeliverySchedulesTableController')->name('delivery_schedules.get');
});
Route::group(['namespace' => 'commission'], function () {
    Route::post('commissions/get_all_commission', 'CommissionsController@get_all_commission')->name('commissions.get_all_commission');
    Route::get('commissions/all_commission', 'CommissionsController@all_commission')->name('commissions.all_commission');
    Route::post('commissions/get_internal_commission', 'CommissionsController@get_internal_commission')->name('commissions.get_internal_commission');
    Route::get('commissions/create_commision_pay', 'CommissionsController@create_commision_pay')->name('commissions.create_commision_pay');
    Route::get('commissions/internal_commission', 'CommissionsController@internal_commission')->name('commissions.internal_commission');
    Route::resource('commissions', 'CommissionsController');
    //For Datatable
    Route::post('commissions/get', 'CommissionsTableController')->name('commissions.get');
});
Route::group(['namespace' => 'third_party_user'], function () {
    Route::resource('third_party_users', 'ThirdPartyUsersController');
    //For Datatable
    Route::post('third_party_users/get', 'ThirdPartyUsersTableController')->name('third_party_users.get');
});
Route::group(['namespace' => 'sell_price'], function () {
    Route::post('sell_prices/change_status/{id}', 'SellPricesController@change_status')->name('sell_prices.change_status');
    Route::get('sell_prices/create_product/{sell_id}', 'SellPricesController@create_product')->name('sell_prices.create_product');
    Route::post('sell_prices/product_link', 'SellPricesController@product_link')->name('sell_prices.product_link');
    Route::get('sell_prices/update_prices/{id}', 'SellPricesController@update_prices')->name('sell_prices.update_prices');
    Route::resource('sell_prices', 'SellPricesController');
    //For Datatable
    Route::post('sell_prices/get', 'SellPricesTableController')->name('sell_prices.get');
});
Route::group(['namespace' => 'import_request'], function () {
    Route::post('import_requests/change_status/{id}', 'ImportRequestsController@change_status')->name('import_requests.change_status');
    Route::post('import_requests/get_products', 'ImportRequestsController@get_products')->name('import_requests.get_products');
    Route::patch('import_requests/update_import_request/{id}', 'ImportRequestsController@update_import_request')->name('import_requests.update_import_request');
    Route::get('import_requests/edit_import_request/{id}', 'ImportRequestsController@edit_import_request')->name('import_requests.edit_import_request');
    Route::resource('import_requests', 'ImportRequestsController');
    //For Datatable
    Route::post('import_requests/get', 'ImportRequestsTableController')->name('import_requests.get');
});
Route::group(['namespace' => 'message_template'], function () {
    Route::resource('message_templates', 'MessageTemplatesController');
    //For Datatable
    Route::post('message_templates/get', 'MessageTemplatesTableController')->name('message_templates.get');
});
Route::group(['namespace' => 'tender'], function () {
    Route::delete('tenders/delete_follow_ups/{id}', 'TendersController@delete_follow_ups')->name('tender.delete_follow_ups');
    Route::post('tenders/get_follow_ups', 'TendersController@get_follow_ups')->name('tender.get_follow_ups');
    Route::post('tenders/update_follow_ups/{id}', 'TendersController@update_follow_ups')->name('tender.update_follow_ups');
    Route::post('tenders/store_follow_ups/{id}', 'TendersController@store_follow_ups')->name('tender.store_follow_ups');
    Route::post('tenders/change_status/{id}', 'TendersController@change_status')->name('tender.change_status');
    Route::resource('tenders', 'TendersController');
    //For Datatable
    Route::post('tenders/get', 'TendersTableController')->name('tenders.get');
});
Route::group(['namespace' => 'rfq_analysis'], function () {
    Route::post('rfq_analysis/notify_suppliers/{id}', 'RfQAnalysisController@notify_suppliers')->name('rfq_analysis.notify_suppliers');
    Route::post('rfq_analysis/approve/{id}', 'RfQAnalysisController@approve')->name('rfq_analysis.approve');
    Route::post('rfq_analysis/select_supplier/{id}', 'RfQAnalysisController@select_supplier')->name('rfq_analysis.select_supplier');
    Route::get('rfq_analysis/create_analysis/{rfq_id}', 'RfQAnalysisController@create_analysis')->name('rfq_analysis.create_analysis');
    Route::resource('rfq_analysis', 'RfQAnalysisController');
    //For Datatable
    Route::post('rfq_analysis/get', 'RfQAnalysisTableController')->name('rfq_analysis.get');
});

Route::group(['namespace' => 'quote_note'], function () {
    Route::post('quote_notes/get_notes', 'QuoteNotesController@get_notes')->name('quote_notes.get_notes');
    Route::resource('quote_notes', 'QuoteNotesController');
    //For Datatable
    Route::post('quote_notes/get', 'QuoteNotesTableController')->name('quote_notes.get');
});
Route::group(['namespace' => 'send_email'], function () {
    Route::resource('send_emails', 'SendEmailsController');
    //For Datatable
    Route::post('send_emails/get', 'SendEmailsTableController')->name('send_emails.get');
});
Route::group(['namespace' => 'send_sms'], function () {
    Route::post('send_sms/get_casuals', 'SendSmsController@get_casuals')->name('send_sms.get_casuals');
    Route::post('send_sms/get_prospects', 'SendSmsController@get_prospects')->name('send_sms.get_prospects');
    Route::post('send_sms/delete_settings', 'SendSmsController@delete_settings')->name('send_sms.delete_settings');
    Route::patch('send_sms/update_settings', 'SendSmsController@update_settings')->name('send_sms.update_settings');
    Route::post('send_sms/get_settings', 'SendSmsController@get_settings')->name('send_sms.get_settings');
    Route::post('send_sms/store_recipents', 'SendSmsController@store_recipents')->name('send_sms.store_recipents');
    Route::post('send_sms/get_all_sms', 'SendSmsController@get_all_sms')->name('send_sms.get_all_sms');
    Route::post('send_sms/activate_deactivate_sms', 'SendSmsController@activate_deactivate_sms')->name('send_sms.activate_deactivate_sms');
    Route::post('send_sms/get_sms_settings', 'SendSmsController@get_sms_settings')->name('send_sms.get_sms_settings');
    Route::get('send_sms/email_&_sms', 'SendSmsController@notification_email_sms')->name('send_sms.notification_email_sms');
    Route::get('send_sms/index_sms_settings', 'SendSmsController@index_sms_settings')->name('send_sms.index_sms_settings');
    Route::get('send_sms/index_send_sms', 'SendSmsController@index_send_sms')->name('send_sms.index_send_sms');
    Route::resource('send_sms', 'SendSmsController');
    //For Datatable
    Route::post('send_sms/get', 'SendSmsTableController')->name('send_sms.get');
});
Route::group(['namespace' => 'bom'], function () {
    Route::post('boms/bom_items', 'BoMsController@bom_items')->name('boms.bom_items');
    Route::post('boqs/get_bom_items', 'BoMsController@get_bom_items')->name('boms.get_bom_items');
    Route::post('boqs/select_bom', 'BoMsController@select_bom')->name('boms.select_bom');
    Route::resource('boms', 'BoMsController');
    //For Datatable
    Route::post('boms/get', 'BoMsTableController')->name('boms.get');
});
Route::group(['namespace' => 'boq'], function () {
    Route::post('boqs/get_boq_products', 'BoQsController@get_boq_products')->name('boqs.get_boq_products');
    Route::post('boqs/get_boq_items', 'BoQsController@get_boq_items')->name('boqs.get_boq_items');
    Route::post('boqs/store_boq_sheet', 'BoQsController@store_boq_sheet')->name('boqs.store_boq_sheet');
    Route::get('boqs/generate_bom/{id}', 'BoQsController@generate_bom')->name('boqs.generate_bom');
    Route::resource('boqs', 'BoQsController');
    //For Datatable
    Route::post('boqs/get', 'BoQsTableController')->name('boqs.get');
});
Route::group(['namespace' => 'job_category'], function () {
    Route::resource('job-categories', 'JobCategoriesController');
    //For Datatable
    Route::post('job-categories/get', 'JobCategoriesTableController')->name('job-categories.get');
});
Route::group(['namespace' => 'jobtitle'], function () {
    Route::post('jobtitles/select', 'JobTitleController@select')->name('jobtitles.select');
    Route::resource('jobtitles', 'JobTitleController');
    //For Datatable
    Route::post('jobtitles/get', 'JobTitleTableController')->name('jobtitles.get');
});

Route::group(['namespace' => 'fault'], function () {
    Route::post('faults/select', 'FaultController@select')->name('faults.select');
    Route::resource('faults', 'FaultController');
    //For Datatable
    Route::post('faults/get', 'FaultTableController')->name('faults.get');
});
Route::group(['namespace' => 'deduction'], function () {
    Route::post('deductions/select', 'DeductionController@select')->name('deductions.select');
    Route::resource('deductions', 'DeductionController');
    //For Datatable
    Route::post('deductions/get', 'DeductionTableController')->name('deductions.get');
});

Route::group(['namespace' => 'deptor'], function () {
    Route::resource('deptors', 'DeptorsController');
    //For Datatable
    Route::post('deptors/get', 'DeptorsTableController')->name('deptors.get');
});

Route::group(['namespace' => 'deptor'], function () {
    Route::resource('deptors', 'DeptorsController');
    //For Datatable
    Route::post('deptors/get', 'DeptorsTableController')->name('deptors.get');
});

Route::group(['namespace' => 'employeesalary'], function () {
    Route::resource('employeesalaries', 'EmployeeSalariesController');
    //For Datatable
    Route::post('salaries/get', 'SalariesTableController')->name('salaries.get');
    Route::post('employeesalaries/get', 'EmployeeSalariesTableController')->name('employeesalaries.get');
});

Route::group(['namespace' => 'queuerequisition'], function () {
    Route::post('queuerequisitions/status', 'QueueRequisitionController@status')->name('queuerequisitions.status');
    Route::post('queuerequisitions/goods', 'QueueRequisitionController@goods')->name('queuerequisitions.goods');
    Route::post('queuerequisitions/select_queuerequisition', 'QueueRequisitionController@select_queuerequisition')->name('queuerequisitions.select_queuerequisition');
    //update_description
    Route::post('queuerequisitions/update_description', 'QueueRequisitionController@update_description')->name('queuerequisitions.update_description');
    Route::post('queuerequisitions/select', 'QueueRequisitionController@select')->name('queuerequisitions.select');
    Route::resource('queuerequisitions', 'QueueRequisitionController');
    //For Datatable
    Route::post('queuerequisitions/get', 'QueueRequisitionTableController')->name('queuerequisitions.get');
    //Route::post('que/get', 'queTableController')->name('que.get');
});

Route::group(['namespace' => 'equipment'], function () {
    Route::resource('equipments', 'EquipmentsController');
    Route::post('equipments/equipment_load', 'EquipmentsController@equipment_load')->name('equipments.equipment_load');
    Route::post('equipments/search/{id}', 'EquipmentsController@equipment_search')->name('equipments.equipment_search');
    Route::post('equipments/attach', 'EquipmentsController@attach')->name('equipments.attach');
    Route::post('equipments/dettach', 'EquipmentsController@dettach')->name('equipments.dettach');

    //For Datatable
    Route::post('equipments/get', 'EquipmentsTableController')->name('equipments.get');
});

Route::group(['namespace' => 'equipmentcategory'], function () {
    Route::resource('equipmentcategories', 'EquipmentCategoriesController');
    //For Datatable
    Route::post('equipmentcategories/get', 'EquipmentCategoriesTableController')->name('equipmentcategories.get');
});
Route::group(['namespace' => 'event'], function () {
    Route::get('events/load_events', 'EventsController@load_events')->name('events.load_events');
    Route::post('events/update_event', 'EventsController@update_event')->name('events.update_event');
    Route::post('events/delete_event', 'EventsController@delete_event')->name('events.delete_event');

    //For Datatable
    Route::post('events/get', 'EventsTableController')->name('events.get');
    Route::resource('events', 'EventsController');
});

Route::group(['namespace' => 'djc'], function () {
    Route::resource('djcs', 'DjcsController');
    Route::get('ssr/default-inputs', 'DjcsController@getSsrDefaultInputs')->name('djcs-default-inputs');

    //For Datatable
    Route::post('djcs/get', 'DjcsTableController')->name('djcs.get');
});

Route::group(['namespace' => 'rjc'], function () {
    Route::post('rjcs/project_extra_details', 'RjcsController@project_extra_details')->name('rjcs.project_extra_details');
    Route::resource('rjcs', 'RjcsController');
    //For Datatable
    Route::post('rjcs/get', 'RjcsTableController')->name('rjcs.get');
});

Route::group(['namespace' => 'jobschedule'], function () {
    Route::resource('jobschedules', 'JobschedulesController');

    Route::post('products/stock_transfer', 'ProductsController@stock_transfer')->name('products.stock_transfer');
    //For Datatable
    Route::post('jobschedules/get', 'JobschedulesTableController')->name('jobschedules.get');
});
 
Route::group(['namespace' => 'omniconvo'], function () {
    Route::get('whatsapp_broadcasts/create', 'OmniController@whatsappBroadcastCreate')->name('whatsapp_broadcast.create');
    Route::get('whatsapp_broadcasts', 'OmniController@whatsappBroadcastIndex')->name('whatsapp_broadcast.index');
    Route::get('media_blocks/create', 'OmniController@mediaBlocksCreate')->name('omniconvo.media_blocks_create');
    Route::get('media_blocks', 'OmniController@mediaBlocksIndex')->name('omniconvo.media_blocks_index');
    Route::get('media_block_template', 'OmniController@downloadUserTemplate')->name('omniconvo.media_block_template');
    Route::post('send_user_message', 'OmniController@sendUserMessage')->name('omniconvo.send_user_message');
});

Route::group(['namespace' => 'whatsapp'], function () {
    Route::get('whatsapp/setup', 'WhatsappController@setup')->name('whatsapp.setup');
    // 
    Route::get('whatsapp/templates', 'WhatsappController@templates')->name('whatsapp.templates.index');
    Route::get('whatsapp/templates/create', 'WhatsappController@templates_create')->name('whatsapp.templates.create');
    Route::post('whatsapp/templates/store', 'WhatsappController@templates_store')->name('whatsapp.templates.store');
    // 
    Route::get('whatsapp/messages', 'WhatsappController@messages')->name('whatsapp.messages.index');
    Route::get('whatsapp/messages/create', 'WhatsappController@messages_create')->name('whatsapp.messages.create');
    Route::post('whatsapp/messages/store', 'WhatsappController@messages_store')->name('whatsapp.messages.store');
});

Route::group(['namespace' => 'ai'], function () {
    Route::get('ai/analytics', 'AIController@analytics')->name('ai.analytics');
});

Route::group(['namespace' => 'lead'], function () {
    // AI agent leads
    Route::get('agent_leads/contacts', 'AgentLeadsController@omniContacts')->name('agent_leads.omni_contacts');
    Route::get('agent_leads/analytics', 'AgentLeadsController@omniAnalyitcs')->name('agent_leads.omni_analytics');
    Route::get('agent_leads/transcripts', 'AgentLeadsController@omniTranscripts')->name('agent_leads.omni_transcripts');
    Route::resource('agent_leads', 'AgentLeadsController');

    Route::post('agent_leads/contacts/get', 'AgentLeadsController@omniContactsDatatable')->name('agent_leads.omni_contacts_get');
    Route::post('agent_leads/get', 'AgentLeadsController@datatable')->name('agent_leads.get');

    Route::get('leads/create_client/{id}', 'LeadsController@create_client')->name('leads.create_client');
    Route::post('leads/get_potentials', 'LeadsController@get_potentials')->name('leads.get_potentials');
    Route::get('leads/index_potential', 'LeadsController@index_potential')->name('leads.index_potential');
    Route::get('leads/download_walkins', 'LeadsController@download_walkins')->name('leads.download_walkins');
    Route::patch('leads/update_status/{lead}', 'LeadsController@update_status')->name('leads.update_status');
    Route::patch('leads/update_reminder/{lead}', 'LeadsController@update_reminder')->name('leads.update_reminder');
    Route::post('leads/lead_search', 'LeadsController@lead_search')->name('leads.lead_search');
    Route::resource('leads', 'LeadsController');
    Route::resource('lead-sources', 'LeadSourceController');
    //For Datatable
    Route::post('leads/get', 'LeadsTableController')->name('leads.get');
});

//Prospects
Route::group(['namespace' => 'prospect'], function () {
    Route::patch('prospects/update_status/{prospect}', 'ProspectsController@update_status')->name('prospects.update_status');
    Route::resource('prospects', 'ProspectsController');

    //For Datatable
    Route::post('prospects/get', 'ProspectsTableController')->name('prospects.get');
    
    Route::post('prospects/followup', 'ProspectsController@followup')->name('prospects.followup');
    Route::post('prospects/fetchprospect', 'ProspectsController@fetchprospect')->name('prospects.fetchprospect');
});
//ProspectsCallResolved
Route::group(['namespace' => 'prospectcallresolved'], function () {
    Route::patch('prospectcallresolves/update_status/{prospect}', 'ProspectsCallResolvedController@update_status')->name('prospectcallresolves.update_status');
    Route::resource('prospectcallresolves', 'ProspectsCallResolvedController');
    Route::post('prospectcallresolves/notpicked','ProspectsCallResolvedController@notpicked')->name('prospectcallresolves.notpicked');
    Route::post('prospectcallresolves/pickedbusy','ProspectsCallResolvedController@pickedbusy')->name('prospectcallresolves.pickedbusy');
    Route::post('prospectcallresolves/notavailable','ProspectsCallResolvedController@notavailable')->name('prospectcallresolves.notavailable');
    Route::resource('prospectscallresolved', 'ProspectsCallResolvedController');
    //For Datatable
    Route::post('prospectcallresolves/get', 'ProspectsCallResolvedTableController')->name('prospectcallresolves.get');
    Route::post('prospectcallresolves/followup', 'ProspectsCallResolvedController@followup')->name('prospectcallresolves.followup');
    Route::post('prospectcallresolves/fetchprospectrecord', 'ProspectsCallResolvedController@fetchprospectrecord')->name('prospectcallresolves.fetchprospectrecord');
});

//CallList
Route::group(['namespace' => 'calllist'], function () {
    
    Route::post('calllists/get_user_call_lists', 'CallListController@get_user_call_lists')->name('calllists.get_user_call_lists');
    Route::post('calllists/store_reassign', 'CallListController@store_reassign')->name('calllists.store_reassign');
    Route::get('calllists/reasign_call_list', 'CallListController@reasign_call_list')->name('calllists.reasign_call_list');
    Route::get('calllists/previous_call_list', 'CallListController@previous_call_list')->name('calllists.previous_call_list');
    Route::post('calllists/get_previous_call_lists', 'CallListController@get_previous_call_lists')->name('calllists.get_previous_call_lists');
    Route::get('calllists/mytoday', 'CallListController@mytoday')->name('calllists.mytoday');
    Route::get('calllists/allocationdays/{id}', 'CallListController@allocationdays')->name('calllists.allocationdays');
    Route::patch('calllists/update_status/{calllist}', 'CallListController@update_status')->name('calllists.update_status');
    Route::resource('calllists', 'CallListController');

    //For Datatable
    
    Route::post('calllists/get', 'CallListTableController')->name('calllists.get');
   
    Route::post('calllists/mytoday', 'MyTodayCallListTableController')->name('calllists.fetchtodaycalls');
    Route::post('calllists/prospectscalllist', 'MyTodayCallListTableController')->name('calllists.prospectcalllist');
    Route::post('calllists/prospectviacalllist', 'CallListController@prospectviacalllist')->name('calllists.prospectviacalllist');
    Route::post('calllists/followup', 'CallListController@followup')->name('calllists.followup');
});

//Remarks
Route::group(['namespace' => 'remark'], function () {
    Route::patch('remarks/update_status/{remark}', 'ProspectsController@update_status')->name('remarks.update_status');
    Route::resource('remarks', 'RemarksController');

    //For Datatable
    // Route::post('remarks/get', 'RemarksTableController')->name('remarks.get');

});
//Prospect Questions
Route::group(['namespace' => 'prospect_question'], function () {
    Route::post('prospect_questions/get_items', 'ProspectQuestionsController@get_items')->name('prospect_questions.get_items');
    Route::resource('prospect_questions', 'ProspectQuestionsController');
    //For Datatable
    Route::post('prospect_questions/get', 'ProspectQuestionsTableController')->name('prospect_questions.get');
});
Route::group(['namespace' => 'lender'], function () {
    Route::resource('lenders', 'LendersController');

    //For Datatable
    Route::post('lenders/get', 'LendersTableController')->name('lenders.get');
});

Route::group(['namespace' => 'loan'], function () {
    Route::get('loans/lender_loans', 'LoansController@lender_loans')->name('loans.lender_loans');
    Route::post('loans/lenders', 'LoansController@lenders')->name('loans.lenders');
    Route::get('loans/pay_loans', 'LoansController@pay_loans')->name('loans.pay_loans');
    Route::post('loans/store_loans', 'LoansController@store_loans')->name('loans.store_loans');
    Route::get('loans/approve/{loan}', 'LoansController@approve_loan')->name('loans.approve_loan');
    Route::resource('loans', 'LoansController');
    //For Datatable
    Route::post('loans/get', 'LoansTableController')->name('loans.get');
});

Route::group(['namespace' => 'journal'], function () {
    Route::post('journals/project_search', 'JournalsController@projectSearch')->name('journals.project_search');
    Route::post('journals/account_names', 'JournalsController@account_names')->name('journals.account_names');
    Route::post('journals/journal_accounts', 'JournalsController@journal_accounts')->name('journals.journal_accounts');
    Route::resource('journals', 'JournalsController');
    //For Datatable
    Route::post('journals/get', 'JournalsTableController')->name('journals.get');
});

Route::group(['namespace' => 'reconciliation'], function () {
    Route::get('reconciliations/print_pdf/{reconciliation}', 'ReconciliationsController@printPDF')->name('reconciliations.print_pdf');
    Route::post('reconciliations/post_uncleared_account_items', 'ReconciliationsController@postUnclearedAccountItems')->name('reconciliations.post_uncleared_account_items');
    Route::post('reconciliations/prev_uncleared_account_items', 'ReconciliationsController@prevUnclearedAccountItems')->name('reconciliations.prev_uncleared_account_items');
    Route::post('reconciliations/account_balance', 'ReconciliationsController@accountBalance')->name('reconciliations.account_balance');
    Route::post('reconciliations/account_items', 'ReconciliationsController@accountItems')->name('reconciliations.account_items');
    Route::resource('reconciliations', 'ReconciliationsController');
    //For Datatable
    Route::post('reconciliations/get', 'ReconciliationsTableController')->name('reconciliations.get');
});


Route::group(['namespace' => 'makepayment'], function () {
    Route::resource('makepayments', 'MakepaymentsController');

    //Route::post('purchases/customer_load', 'PurchasesController@customer_load')->name('purchases.customer_load');

    //For Datatable
    Route::get('makepayment/single_payment/{tr_id}', 'MakepaymentsController@single_payment')->name('makepayment.single_payment');
    Route::get('makepayment/receive_single_payment/{tr_id}', 'MakepaymentsController@receive_single_payment')->name('makepayment.receive_single_payment');
});



Route::group(['namespace' => 'misc'], function () {
    Route::resource('miscs', 'MiscsController');
    //For Datatable
    Route::post('miscs/get', 'MiscsTableController')->name('miscs.get');
});
Route::group(['namespace' => 'note'], function () {
    Route::resource('notes', 'NotesController');
    //For Datatable
    Route::post('notes/get', 'NotesTableController')->name('notes.get');
});


Route::group(['namespace' => 'order'], function () {
    Route::resource('orders', 'OrdersController');
    //For Datatable
    Route::post('orders/get', 'OrdersTableController')->name('orders.get');
});
Route::group(['namespace' => 'openingbalance'], function () {
    Route::resource('openingbalances', 'OpeningbalancesController');
    //For Datatable
    //Route::post('productstocktransfers/get', 'ProductstocktransfersTableController')->name('productstocktransfers.get');
});

Route::group(['namespace' => 'prefix'], function () {
    Route::resource('prefixes', 'PrefixesController');
    //For Datatable
    Route::post('prefixes/get', 'PrefixesTableController')->name('prefixes.get');
});
Route::group(['namespace' => 'pricegroup'], function () {
    Route::resource('pricegroups', 'PricegroupsController');
    //For Datatable
    Route::post('pricegroups/get', 'PricegroupsTableController')->name('pricegroups.get');
});

Route::group(['namespace' => 'client_product'], function () {
    Route::post('client_products/store_code', 'ClientProductsController@store_code')->name('client_products.store_code');
    Route::resource('client_products', 'ClientProductsController');
    //For Datatable
    Route::post('client_products/get', 'ClientProductsTableController')->name('client_products.get');
});

Route::group(['namespace' => 'pricelistSupplier'], function () {
    Route::post('pricelistsSupplier/change_status/{supplier_product_id}', 'PriceListsController@change_status')->name('pricelistsSupplier.change_status');
    Route::post('pricelistsSupplier/change_attachment/{supplier_product_id}', 'PriceListsController@change_attachment')->name('pricelistsSupplier.change_attachment');
    Route::get('pricelistsSupplier/list', 'PriceListsController@list')->name('pricelistsSupplier.list');
    Route::resource('pricelistsSupplier', 'PriceListsController');
    //For Datatable
    Route::post('pricelists/get', 'PriceListTableController')->name('pricelistsSupplier.get');
    Route::post('pricelists/gets', 'SupplierPriceListTableController')->name('pricelistsSupplier.gets');
});

Route::group(['namespace' => 'productcategory'], function () {
    Route::get('productcategories/search_code/{code}', 'ProductcategoriesController@search_code')->name('productcategories.search_code');
    Route::resource('productcategories', 'ProductcategoriesController');
    //For Datatable
    Route::post('productcategories/get', 'ProductcategoriesTableController')->name('productcategories.get');
});
Route::group(['namespace' => 'projectstocktransfer'], function () {
    Route::resource('projectstocktransfers', 'ProjectstocktransfersController');
    //For Datatable
    Route::post('projectstocktransfers/get', 'ProjectstocktransfersTableController')->name('projectstocktransfers.get');
});

Route::group(['namespace' => 'lpo'], function () {
    Route::post('lpo/update_lpo', 'LpoController@update_lpo')->name('lpo.update_lpo');
    Route::get('lpo/delete_lpo/{id}', 'LpoController@delete_lpo')->name('lpo.delete_lpo');

    Route::resource('lpo', 'LpoController');
    // for dataTable
    Route::post('lpo/get', 'LpoTableController')->name('lpo.get');
});

Route::group(['namespace' => 'productvariable'], function () {
    Route::resource('productvariables', 'ProductvariablesController');
    //For Datatable
    Route::post('productvariables/get', 'ProductvariablesTableController')->name('productvariables.get');
});
Route::group(['namespace' => 'purchase'], function () {
    Route::post('purchases/accounts_select', 'PurchasesController@accounts_select')->name('purchases.accounts_select');
    Route::post('purchases/customer_load', 'PurchasesController@customer_load')->name('purchases.customer_load');
    Route::post('purchases/quote', 'PurchasesController@quote_product_search')->name('purchase.quote_purchase_search');
    Route::resource('purchases', 'PurchasesController');

    //For Datatable
    Route::post('purchases/get', 'PurchasesTableController')->name('purchases.get');
});

Route::group(['namespace' => 'PurchaseClass'], function () {

    Route::resource('purchase-classes', 'PurchaseClassController');
    Route::get('purchase-class-breviary', [PurchaseClassController::class, 'purchaseClassBreviary'])->name('purchase-class-breviary');
    Route::get('purchase-class-breviary-callback', [PurchaseClassController::class, 'breviaryCallback'])->name('purchase-class-breviary-callback');

    Route::get('purchase-class-reclassify', [PurchaseClassController::class, 'purchaseClassReclassify'])->name('purchase-class-reclassify');
    Route::post('reclassify-pc-purchases', [PurchaseClassController::class, 'reclassifyPurchases'])->name('reclassify-pc-purchases');


});

Route::group(['namespace' => 'expenseCategory'], function () {

    Route::resource('expense-category', 'ExpenseCategoryController');
});

Route::group(['namespace' => 'PurchaseClassBudget'], function () {

    Route::resource('purchase-class-budgets', 'PurchaseClassBudgetController');
    Route::get('purchase-class-budget/get-reports', 'PurchaseClassBudgetController@reportIndex')->name('purchase_class_budgets.get-reports');
    Route::post('purchase-class-budget/{id}/get-purchases-data', 'PurchaseClassBudgetController@getPurchasesData')->name('purchase_class_budgets.get-purchases-data');
    Route::post('purchase-class-budget/{id}/get-purchase-orders-data', 'PurchaseClassBudgetController@getPurchaseOrdersData')->name('purchase_class_budgets.get-purchase-orders-data');
    Route::get('purchase-class-budget/metrics', 'PurchaseClassBudgetController@metrics')->name('purchase-class-budgets.metrics');
    Route::get('purchase-class-budget/department/{departmentId}/chart-metrics', 'PurchaseClassBudgetController@chartMetrics')->name('purchase-class-budgets.chart-metrics');
    Route::get('purchase-class-budget/purchases-metrics', 'PurchaseClassBudgetController@getPurchasesMetrics')->name('purchase-class-budgets.purchases-metrics');
    Route::get('purchase-class-budget/purchase-orders-metrics', 'PurchaseClassBudgetController@getPurchaseOrdersMetrics')->name('purchase-class-budgets.purchase-orders-metrics');

});


Route::group(['namespace' => 'projectequipment'], function () {
    Route::resource('projectequipments', 'ProjectequipmentsController');
    Route::post('projectequipments/write_job_card', 'ProjectequipmentsController@write_job_card')->name('projectequipments.write_job_card');
    //For Datatable
    Route::post('projectequipments/get', 'ProjectequipmentsTableController')->name('projectequipments.get');
});
Route::group(['namespace' => 'quote'], function () {
    Route::post('quotes/delete_quote_file/{quote_id}', 'QuotesController@delete_quote_file')->name('quotes.delete_quote_file');
    Route::post('quotes/store_attachment/{quote_id}', 'QuotesController@store_attachment')->name('quotes.store_attachment');
    Route::post('quotes/quote_download', 'QuotesController@quote_download')->name('quotes.quote_download');
    Route::post('quotes/send_email', 'QuotesController@send_email')->name('quotes.send_email');
    Route::post('quotes/convert', 'QuotesController@convert')->name('quotes.convert');
    Route::post('quotes/approve_quote/{quote}', 'QuotesController@approve_quote')->name('quotes.approve_quote');

    Route::post('quotes/close_quote/{quote}', 'QuotesController@close_quote')->name('quotes.close_quote');
    Route::post('quotes/storeverified', 'QuotesController@storeverified')->name('quotes.storeverified');
    Route::get('quotes/customer_quotes', 'QuotesController@customer_quotes')->name('quotes.customer_quotes');
    Route::get('quotes/verify/{quote}', 'QuotesController@verify_quote')->name('quotes.verify');
    Route::post('quotes/verified_jcs/{id}', 'QuotesController@fetch_verified_jcs')->name('quotes.fetch_verified_jcs');
    Route::get('quotes/verification', 'QuotesController@verificationIndex')->name('quotes.verification');
    Route::get('quotes/turn_around', 'QuotesController@turn_around')->name('quotes.turn_around');

    // should be delete methods
    Route::get('quotes/delete_product/{id}', 'QuotesController@delete_product')->name('quotes.delete_product');
    Route::get('quotes/verified_item/{id}', 'QuotesController@delete_verified_item')->name('quotes.delete_verified_item');
    Route::get('quotes/verified_jcs/{id}', 'QuotesController@delete_verified_jcs')->name('quotes.delete_verified_jcs');
    Route::get('quotes/reset_verified/{id}', 'QuotesController@reset_verified')->name('quotes.reset_verified');

    Route::post('quotes/send_single_sms', 'QuotesController@send_single_sms')->name('quotes.send_single_sms');
    Route::post('quotes/lpo', 'QuotesController@update_lpo')->name('quotes.lpo');
    Route::resource('quotes', 'QuotesController');
    //For Datatable
    Route::post('quotes/get_project', 'QuoteVerifyTableController')->name('quotes.get_project');
    Route::post('quotes/get', 'QuotesTableController')->name('quotes.get');
    Route::post('turn_around/search', 'TurnAroundTimeTableController')->name('turn_around.search');

    Route::post('send_link_budget', 'QuoteBudgetController@send_link_budget')->name('send_link_budget');
    Route::get('quotes-approved-budgets', 'QuoteBudgetController@index')->name('quotes-approved-budgets');


});

Route::group(['namespace' => 'template_quote'], function () {
    Route::resource('template-quotes', 'TemplateQuoteController');
    Route::post('template-quotes/get', 'TemplateQuoteTableController')->name('template-quotes.get');
    Route::post('template-quote/details','TemplateQuoteController@getTemplateQuoteDetails')->name('template-quote-details');
   
});

Route::group(['namespace' => 'rfq'], function () {
    Route::post('rfq/get_items', 'RfQController@get_items')->name('rfq.get_items');
    Route::post('rfq/send_sms_and_email/{id}', 'RfQController@send_sms_and_email')->name('rfq.send_sms_and_email');
    Route::post('rfq/approve/{id}', 'RfQController@approve')->name('rfq.approve');
    Route::resource('rfq', 'RfQController');
    Route::post('rfq/get', 'RfQTableController')->name('rfq.get');
     Route::get('rfq/generate_from_budget/{budgetId}', 'RfQController@create')->name('print-rfq');
     Route::post('print-rfq/{rfqId}', 'RfQController@printRfq')->name('print-rfq');


    // Route::post('template-quote/details','TemplateQuoteController@getTemplateQuoteDetails')->name('template-quote-details');

});

// partial verification
Route::group(['namespace' => 'verification'], function () {
    Route::get('verifications/quote_index', 'VerificationsController@quote_index')->name('verifications.quote_index');
    Route::resource('verifications', 'VerificationsController');
    //For Datatable
    Route::post('verifications/get', 'VerificationsTableController')->name('verifications.get');
    Route::post('verifications/quotes/get', 'VerificationQuotesTableController')->name('verifications.get_quotes');
});

Route::group(['namespace' => 'region'], function () {
    Route::resource('regions', 'RegionsController');
    Route::post('regions/load_region', 'RegionsController@load_region')->name('regions.load_region');

    Route::post('regions/get', 'RegionsTableController')->name('regions.get');
});

Route::group(['namespace' => 'section'], function () {
    Route::resource('sections', 'SectionsController');

    Route::post('sections/get', 'SectionsTableController')->name('sections.get');
});

Route::group(['namespace' => 'spvariations'], function () {
    Route::resource('spvariations', 'SpVariablesController');
    //For Datatable
    Route::post('spvariations/get', 'SpVariablesControllerTableController')->name('spvariations.get');
});
Route::group(['namespace' => 'template'], function () {
    Route::resource('templates', 'TemplatesController');
    //For Datatable
    Route::post('templates/get', 'TemplatesTableController')->name('templates.get');
});
Route::group(['namespace' => 'term'], function () {
    Route::resource('terms', 'TermsController');
    //For Datatable
    Route::post('terms/get', 'TermsTableController')->name('terms.get');
});

Route::group(['namespace' => 'transactioncategory'], function () {
    Route::resource('transactioncategories', 'TransactioncategoriesController');
    //For Datatable
    Route::post('transactioncategories/get', 'TransactioncategoriesTableController')->name('transactioncategories.get');
});

Route::group(['namespace' => 'gateway'], function () {
    Route::resource('usergatewayentries', 'UsergatewayentriesController');
    //For Datatable
    Route::post('usergatewayentries/get', 'UsergatewayentriesTableController')->name('usergatewayentries.get');
});
Route::group(['namespace' => 'warehouse'], function () {
    Route::resource('warehouses', 'WarehousesController');
    //For Datatable
    Route::post('warehouses/get', 'WarehousesTableController')->name('warehouses.get');
});

Route::group(['namespace' => 'withholding'], function () {
    Route::post('withholdings/select_invoices', 'WithholdingsController@select_invoices')->name('withholdings.select_invoices');
    Route::post('withholdings/select_unallocated_wh_tax', 'WithholdingsController@select_unallocated_wh_tax')->name('withholdings.select_unallocated_wh_tax');
    Route::resource('withholdings', 'WithholdingsController');
    //For Datatable
    Route::post('withholdings/get', 'WithholdingsTableController')->name('withholdings.get');
});

Route::group(['namespace' => 'classlist'], function () {
    Route::resource('classlists', 'ClasslistsController');
    // for DataTable
    Route::post('classlists/get', 'ClasslistsTableController')->name('classlists.get');
});

Route::group(['namespace' => 'financial_year'], function () {
    Route::resource('financial_years', 'FinancialYearController');
    Route::get('fin-year-months', 'FinancialYearController@getFinancialYearMonths')->name('financial-year-months');
});

Route::group(['namespace' => 'documentManager'], function () {
    Route::resource('document-tracker', 'DocumentManagerController');
    Route::get('document-tracker/{id}/trash', 'DocumentManagerController@destroy')->name('trash-document-tracker');
});

Route::group(['namespace' => 'documentBoard'], function () {
    Route::get('/company-notice-board', [DocumentBoardController::class, 'index'])->name('company-notice-board.index');

    Route::get('/company-notice-board/central', [DocumentBoardController::class, 'central'])->name('company-notice-board.central');

    Route::get('/company-notice-board/create-welcome', [DocumentBoardController::class, 'createWelcome'])->name('company-notice-board.create-welcome');
    Route::post('/company-notice-board/store-welcome', [DocumentBoardController::class, 'storeWelcome'])->name('company-notice-board.store-welcome');
    Route::get('welcome-message-photo/{filename}', [DocumentBoardController::class, 'showWelcomeImage'])->name('show-welcome-image');

    Route::get('/company-notice-board/notice/create', [DocumentBoardController::class, 'createNotice'])->name('company-notice-board.create-notice');
    Route::post('/company-notice-board/store-notice', [DocumentBoardController::class, 'storeNotice'])->name('company-notice-board.store-notice');



    Route::get('/company-notice-board/create', [DocumentBoardController::class, 'create'])->name('company-notice-board.create');
    Route::post('/company-notice-board', [DocumentBoardController::class, 'store'])->name('company-notice-board.store');
    Route::get('/company-notice-board/view/{documentBoard}', [DocumentBoardController::class, 'view'])->name('company-notice-board.view');
    Route::get('/company-notice-board/download/{documentBoard}', [DocumentBoardController::class, 'download'])->name('company-notice-board.download');
    Route::delete('/company-notice-board/{documentBoard}', [DocumentBoardController::class, 'destroy'])->name('company-notice-board.destroy');


});

Route::group(['namespace' => 'marquee'], function () {

    Route::resource('marquee', 'MarqueeController');
    Route::delete('/delete-marquee/{id}', [MarqueeController::class, 'destroy'])->name('marquee-delete');
    Route::get('/delete-user-marquee/{id}', [MarqueeController::class, 'destroy'])->name('delete-user-marquee');
    Route::get('/old-user-marquees', [MarqueeController::class, 'oldUserMarqueesTable'])->name('old-user-marquees');
    Route::get('/old-admin-marquees', [MarqueeController::class, 'oldSuperAdminMarqueesTable'])->name('old-admin-marquees');
});

Route::group(['namespace' => 'employeeAppraisal'], function () {
    Route::post('employee_appraisals/performance_evaluation/{appraisals}', 'EmployeeAppraisalController@performance_evaluation')->name('employee_appraisals.performance_evaluation');
    Route::resource('employee_appraisals', 'EmployeeAppraisalController');
});

Route::group(['namespace' => 'appraisal_type'], function () {
    Route::resource('appraisal_types', 'AppraisalTypesController');
    //For Datatable
    Route::post('appraisal_types/get', 'AppraisalTypesTableController')->name('appraisal_types.get');
});

Route::group(['namespace' => 'jobGrade'], function () {
    Route::resource('job-grades', 'JobGradeController');
});


Route::group(['namespace' => 'package'], function () {
    Route::resource('subscription-packages', 'PackageController');
});


Route::group(['namespace' => 'dailyBusinessMetrics'], function () {


    Route::get('daily-business-metrics/{dbmUuid}', [DailyBusinessMetricController::class, 'redirectToMetrics']);

    Route::get('dbm/customize/set-options', [DailyBusinessMetricController::class, 'setOptions'])->name('dbm-set-options');
    Route::post('dbm/customize/update-options', [DailyBusinessMetricController::class, 'updateOptions'])->name('dbm-update-options');
});

Route::group(['namespace' => 'clientBalance'], function () {

    Route::resource('client-balances', 'ClientBalanceController');
});

Route::group(['namespace' => 'supplierBalance'], function () {

    Route::resource('supplier-outstanding', 'SupplierBalanceController');
});


Route::group(['namespace' => 'employeeNotice'], function () {

    Route::resource('employee-notice', 'EmployeeNoticeController');
    Route::get('/employee-notice/download/{notice}', [EmployeeNoticeController::class, 'download'])->name('employee-notice.download');

});

Route::group(['namespace' => 'calendar'], function () {

    Route::resource('calendar', 'CalendarController');

});

Route::group(['namespace' => 'stakeholders'], function () {

    Route::resource('stakeholders', 'StakeholderController');

});

Route::group(['namespace' => 'recentCustomer'], function () {

    Route::resource('recent-customers', 'RecentCustomerController');

    Route::get('/recent-customer/contact/{customerId}', [RecentCustomerController::class, 'contact'])->name('contact-recent-customer');

    Route::get('/prospect/contact', [RecentCustomerController::class, 'contactProspect'])->name('contact-prospect');

    Route::post('/recent-customer/send-email/{customerId}', [RecentCustomerController::class, 'sendEmail'])->name('email-recent-customer');
    Route::post('/recent-customer/send-sms/{customerId}', [RecentCustomerController::class, 'sendSms'])->name('sms-recent-customer');

    Route::post('/prospect/send-email', [RecentCustomerController::class, 'sendEmail'])->name('email-prospect');
    Route::post('/prospect/send-sms', [RecentCustomerController::class, 'sendSms'])->name('sms-prospect');


    Route::get('/recent-customer-messages', [RecentCustomerController::class, 'showRecentMessages'])->name('recent-customer-messages');

    Route::get('/recent-customer-sms-table', [RecentCustomerController::class, 'smsTable'])->name('recent-customer-sms-table');
    Route::get('/recent-customer-email-table', [RecentCustomerController::class, 'emailTable'])->name('recent-customer-email-table');

    Route::get('/show-recent-customer-email/{messageId}', [RecentCustomerController::class, 'showEmail'])->name('show-recent-customer-email');
    Route::get('/show-recent-customer-sms/{messageId}', [RecentCustomerController::class, 'showSms'])->name('show-recent-customer-sms');

});


Route::group(['namespace' => 'promotions'], function () {

    Route::get('/promotions/assign-codes-to-all-companies', [CompanyPromotionalPrefixController::class, 'assignCodesToAllCompanies'])->name('assign-codes-to-all-companies');

    Route::resource('promotional-codes', 'PromotionalCodeController');

    // In routes/web.php or routes/api.php
    Route::get('get-promo-products', [PromotionalCodeController::class, 'getProducts'])->name('get-promo-products');

    Route::patch('promotions/{id}/update-status', [PromotionalCodeController::class, 'updateStatus'])->name('promotions.update-status');
    Route::post('check-promo-code', [PromotionalCodeController::class, 'checkPromoCodeAvailability'])->name('check-promo-code');

    Route::get('delete-promo-code/{id}', [PromotionalCodeController::class, 'destroy'])->name('delete-promo-code');


    Route::resource('reserve-promo-codes', 'PromoCodeReservationController');

    Route::get('reserve-customer-promo-code', [PromoCodeReservationController::class, 'createCustomerReservation'])->name('reserve-customer-promo-code');
    Route::post('save-reserved-customer-promo-code', [PromoCodeReservationController::class, 'reserveForCustomer'])->name('save-reserved-customer-promo-code');

    Route::get('reserve-3p-promo-code', [PromoCodeReservationController::class, 'createThirdPartyReservation'])->name('reserve-3p-promo-code');
    Route::post('save-reserved-3p-promo-code', [PromoCodeReservationController::class, 'reserveForThirdParty'])->name('save-reserved-3p-promo-code');

    Route::get('show-reserve-customer-promo-code/{id}', [PromoCodeReservationController::class, 'showCustomerReservation'])->name('show-reserve-customer-promo-code');
    Route::get('edit-reserve-customer-promo-code/{id}', [PromoCodeReservationController::class, 'editCustomerReservation'])->name('edit-reserve-customer-promo-code');
    Route::put('update-reserve-customer-promo-code/{id}', [PromoCodeReservationController::class, 'updateCustomerReservation'])->name('update-reserve-customer-promo-code');

    Route::get('show-reserve-3p-promo-code/{id}', [PromoCodeReservationController::class, 'showThirdPartyReservation'])->name('show-reserve-3p-promo-code');
    Route::get('edit-reserve-3p-promo-code/{id}', [PromoCodeReservationController::class, 'editThirdPartyReservation'])->name('edit-reserve-3p-promo-code');
    Route::put('update-reserve-3p-promo-code/{id}', [PromoCodeReservationController::class, 'updateThirdPartyReservation'])->name('update-reserve-3p-promo-code');

    Route::get('show-reserve-referral-promo-code/{id}', [PromoCodeReservationController::class, 'showReferralReservation'])->name('show-reserve-referral-promo-code');
    Route::get('edit-reserve-referral-promo-code/{id}', [PromoCodeReservationController::class, 'editReferralReservation'])->name('edit-reserve-referral-promo-code');
    Route::put('update-reserve-referral-promo-code/{id}', [PromoCodeReservationController::class, 'updateReferralReservation'])->name('update-reserve-referral-promo-code');

    Route::get('get-customer-reservations-data', [PromoCodeReservationController::class, 'getCustomerReservationsData'])->name('get-customer-reservations-data');

    Route::get('get-customer-reservations', [PromoCodeReservationController::class, 'getCustomerReservations'])->name('get-customer-reservations');
    Route::get('get-3p-reservations', [PromoCodeReservationController::class, 'getThirdPartiesReservations'])->name('get-3p-reservations');

    Route::resource('client-feedback', 'ClientFeedbackController');
    Route::get('delete-client-feedback/{id}', [ClientFeedbackController::class, 'destroy'])->name('delete-client-feedback');
    Route::get('download-feedback-file/{id}', [ClientFeedbackController::class, 'download'])->name('download-feedback-file');

    Route::get('get-referrals-table', [PromoCodeReservationController::class, 'getReferralsTable'])->name('get-referrals-table');
    Route::get('referrals-index', [PromoCodeReservationController::class, 'referralsIndex'])->name('referrals-index');
    Route::get('promotions/index_commission', [PromoCodeReservationController::class, 'index_commission'])->name('promotions.index_commission');
    Route::get('promotions/create_commision_pay', [PromoCodeReservationController::class, 'create_commision_pay'])->name('promotions.create_commision_pay');
    Route::post('promotions/get_reservations', [PromoCodeReservationController::class, 'get_reservations'])->name('promotions.get_reservations');
});

Route::group(['namespace' => 'projectSir'], function () {

    Route::resource('project-sir', 'ProjectSirController');

    Route::get('get-sir-projects-summary', [\App\Http\Controllers\Focus\projectSir\ProjectSirController::class, 'getProjectsSummary'])->name('get-sir-projects-summary');
    Route::get('get-sir-specifics-summary', [\App\Http\Controllers\Focus\projectSir\ProjectSirController::class, 'getSpecificsSummary'])->name('get-sir-specifics-summary');

    Route::get('project-sir-table', [\App\Http\Controllers\Focus\projectSir\ProjectSirController::class, 'getProjectsDataTable'])->name('project-sir-table');
    Route::get('project-sir-specifics-table', [\App\Http\Controllers\Focus\projectSir\ProjectSirController::class, 'getSpecificsDataTable'])->name('project-sir-specifics-table');

    Route::get('print-project-sir', [\App\Http\Controllers\Focus\projectSir\ProjectSirController::class, 'printSir'])->name('print-project-sir');

});
