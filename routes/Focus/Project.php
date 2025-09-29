<?php

// project stock
Route::group(['namespace' => 'projectstock'], function () {
  Route::get('projectstock/quotes', 'ProjectStockController@quote_index')->name('projectstock.quote_index');
  Route::resource('projectstock', 'ProjectStockController');
  // data table
  Route::post('projectstock/get_quote', 'QuoteTableController')->name('projectstock.get_quote');
  Route::post('projectstock/get', 'ProjectStockTableController')->name('projectstock.get');
});

//
Route::group(['namespace' => 'project', 'middleware' => 'project'], function () {
  Route::post('projects/update_status', 'ProjectsController@update_status')->name('projects.update_status');
  Route::get('projects/edit_meta', 'ProjectsController@edit_meta')->name('projects.edit_meta');
  Route::post('projects/update_meta', 'ProjectsController@update_meta')->name('projects.update_meta');
  Route::post('projects/store_meta', 'ProjectsController@store_meta')->name('projects.store_meta');
  Route::post('projects/delete_meta', 'ProjectsController@delete_meta')->name('projects.delete_meta');
  Route::post('projects/log_history', 'ProjectsController@log_history')->name('projects.log_history');
  Route::post('projects/notes', 'ProjectsController@notes')->name('projects.notes');
  Route::post('projects/project_budget', 'ProjectsController@project_budget')->name('projects.project_budget');
  Route::post('projects/quotes_select', 'ProjectsController@quotes_select')->name('projects.quotes_select');
  Route::post('projects/invoices_select', 'ProjectsController@invoices_select')->name('projects.invoices_select');
  Route::post('projects/select_detached_invoices', 'ProjectsController@select_detached_invoices')->name('projects.select_detached_invoices');
  Route::post('projects/detach_quote', 'ProjectsController@detach_quote')->name('projects.detach_quote');
  Route::post('projects/detach_budget', 'ProjectsController@detach_budget')->name('projects.detach_budget');
  Route::post('projects/detach_invoice', 'ProjectsController@detach_invoice')->name('projects.detach_invoice');
  Route::post('projects/store_quote_invoice', 'ProjectsController@store_quote_invoice')->name('projects.store_quote_invoice');

  // project budget
  Route::get('projects/budget/{quote}', 'ProjectsController@create_project_budget')->name('projects.create_project_budget');
  Route::get('projects/budget/{qoute_id}/{budget_id}', 'ProjectsController@edit_project_budget')->name('projects.edit_project_budget');
  Route::get('projects/budget', 'ProjectsController@view_budget')->name('projects.view_budget');
  Route::get('projects/get-project-report', 'ProjectsController@get_project_report')->name('projects.get-project-report');
  Route::get('projects/budget_limit/{project}', 'ProjectsController@budget_limit')->name('projects.budget_limit');
  Route::post('projects/budget_store', 'ProjectsController@store_project_budget')->name('projects.store_project_budget');
  Route::post('projects/get_all_report', 'ProjectsController@get_all_report')->name('projects.get_all_report');
  Route::post('projects/budget_update/{budget}', 'ProjectsController@update_project_budget')->name('projects.update_project_budget');
  Route::post('projects/budget_tool_update/{budget}', 'ProjectsController@update_budget_tool')->name('projects.update_budget_tool');
  Route::get('projects/print-gross-profit/{project}', 'ProjectsController@printGrossProfit')->name('projects.print-gross-profit');

    // expenses
  Route::post('projects/expenses', 'ExpensesTableController')->name('projects.get_expense');
});

// project budget
Route::group(['namespace' => 'budget'], function () {
  Route::resource('budgets', 'BudgetsController');
  // data table
  Route::post('budgets/get', 'BudgetsTableController')->name('budgets.get');
});
Route::group(['namespace' => 'milestone'], function () {
  Route::post('milestones/items', 'MilestonesController@items')->name('milestones.items');
  Route::post('milestones/milestone_unallocated_items', 'MilestonesController@milestone_unallocated_items')->name('milestones.milestone_unallocated_items');
  Route::post('milestones/get_requisitions', 'MilestonesController@get_requisitions')->name('milestones.get_requisitions');
  Route::post('milestones/get_milestone_items', 'MilestonesController@get_milestone_items')->name('milestones.get_milestone_items');
  Route::post('milestones/get_budget_items', 'MilestonesController@get_budget_items')->name('milestones.get_budget_items');
  Route::get('milestones/create_milestone/{project_id}', 'MilestonesController@create_milestone')->name('milestones.create_milestone');
  Route::resource('milestones', 'MilestonesController');
});

Route::group(['namespace' => 'labour_allocation'], function () {
  Route::post('labour_allocations/get_casuals', 'LabourAllocationsController@getCasuals')->name('labour_allocations.get_casuals');
  Route::post('labour_allocations/casuals_select', 'LabourAllocationsController@selectCasuals')->name('labour_allocations.casuals_select');
  Route::get('labour_allocations/employee_hourly_rate', 'LabourAllocationsController@employee_hourly_rate')->name('labour_allocations.employee_hourly_rate');
  Route::get('labour_allocations/expected_hours', 'LabourAllocationsController@expected_hours')->name('labour_allocations.expected_hours');
  Route::get('labour_allocations/employee_summary', 'LabourAllocationsController@employee_summary')->name('labour_allocations.employee_summary');
  Route::get('labour_allocations/delete_item/{id}', 'LabourAllocationsController@delete_item')->name('labour_allocations.delete_item');
  Route::get('labour_allocations/delete_labour/{id}', 'LabourAllocationsController@delete_labour')->name('labour_allocations.delete_labour');
  Route::get('labour_allocations/edit_item/{id}', 'LabourAllocationsController@edit_item')->name('labour_allocations.edit_item');
  Route::patch('labour_allocations/update_item/{id}', 'LabourAllocationsController@update_item')->name('labour_allocations.update_item');
  Route::post('labour_allocations/store_labour_items', 'LabourAllocationsController@store_labour_items')->name('labour_allocations.store_labour_items');
  Route::post('labour_allocations/get_employee_items', 'LabourAllocationsController@get_employee_items')->name('labour_allocations.get_employee_items');
  Route::get('labour_allocations/attach_employee/{id}/{employee_id}', 'LabourAllocationsController@attach_employee')->name('labour_allocations.attach_employee');
  Route::resource('labour_allocations', 'LabourAllocationsController');
  // data table
  Route::post('labour_allocations/get', 'LabourAllocationsTableController')->name('labour_allocations.get');
  Route::post('labour_allocations/get_summary', 'EmployeeSummaryTableController')->name('labour_allocations.get_summary');
  Route::post('labour_allocations/get_labour', 'LabourProjectTableController')->name('labour_allocations.get_labour');
  Route::post('casual_remunerations/casual_wages', 'CasualWagesTableController')->name('labour_allocations.get_casual_wages');
});

// project task schedules
Route::group(['namespace' => 'taskschedule'], function () {
  Route::post('taskschedules/quote_product_search', 'TaskSchedulesController@quote_product_search')->name('taskschedules.quote_product_search');
  Route::resource('taskschedules', 'TaskSchedulesController');
  // data table
  Route::post('taskschedules/get', 'TaskSchedulesTableController')->name('taskschedules.get');
});

// project contract service
Route::group(['namespace' => 'contractservice'], function () {
  Route::get('contractservices/serviced_equipment', 'ContractServicesController@serviced_equipment')->name('contractservices.serviced_equipment');
  Route::post('contractservices/service_product_search', 'ContractServicesController@service_product_search')->name('contractservices.service_product_search');
  Route::resource('contractservices', 'ContractServicesController');
  // data table
  Route::post('contractservices/get_equipments', 'EquipmentsTableController')->name('contractservices.get_equipments');
  Route::post('contractservices/get', 'ContractServicesTableController')->name('contractservices.get');
});

// project contract
Route::group(['namespace' => 'contract'], function () {
  Route::get('contracts/create_add_equipment', 'ContractsController@create_add_equipment')->name('contracts.create_add_equipment');
  Route::post('contracts/store_add_equipment', 'ContractsController@store_add_equipment')->name('contracts.store_add_equipment');

  Route::post('contracts/customer_contracts', 'ContractsController@customer_contracts')->name('contracts.customer_contracts');
  Route::post('contracts/task_schedules', 'ContractsController@task_schedules')->name('contracts.task_schedules');
  Route::post('contracts/contract_equipment', 'ContractsController@contract_equipment')->name('contracts.contract_equipment');
  Route::post('contracts/customer_equipment', 'ContractsController@customer_equipment')->name('contracts.customer_equipment');
  Route::resource('contracts', 'ContractsController');
  // data table
  Route::post('contracts/get', 'ContractsTableController')->name('contracts.get');
});

// project
Route::group(['namespace' => 'project'], function () {
  Route::post('projects/status_tag_update', 'ProjectsController@status_tag_update')->name('projects.status_tag_update');
  Route::post('projects/search', 'ProjectsController@search')->name('projects.search');
  Route::resource('projects', 'ProjectsController');
  // data table
  Route::post('projects/get', 'ProjectsTableController')->name('projects.get');
  Route::post('projects/load_select', 'ProjectsController@loadSelect')->name('projects.project_load_select');
  Route::post('projects/search', 'ProjectsController@project_search')->name('projects.project_search');

  Route::get('projects/milestones/get', 'ProjectsController@getProjectMileStones')->name('getProjectMileStones');
  Route::get('projects/quote-milestones/get', 'ProjectsController@getBudgetLinesByQuote')->name('getBudgetLinesByQuote');
  Route::get('/gebm/{projectId}', 'ProjectsController@getExpensesByMilestone');
});

Route::group(['namespace' => 'casual_remuneration'], function () {
  Route::post('casual_remunerations/get', 'CasualRemunerationsTableController')->name('casual_remunerations.get_data');

  Route::resource('casual_remunerations', 'CasualRemunerationsController');
  Route::get('clr/{clr_number}/trash', 'CasualRemunerationsController@destroy')->name('trash-clr');
  Route::post('clr/{clr_number}/approval', 'CasualRemunerationsController@updateApproval')->name('clr-approval');
  Route::get('clr/job_card/details', 'CasualRemunerationsController@getJobCardDetails')->name('clr_job_card_details');
});
