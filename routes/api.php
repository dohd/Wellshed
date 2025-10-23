<?php

use App\Http\Controllers\Focus\bank_feed\BankFeedsController;
use App\Http\Controllers\Focus\dailyBusinessMetrics\DailyBusinessMetricController;
use App\Http\Controllers\Focus\employeeDailyLog\EmployeeDailyLogController;
use App\Http\Controllers\Focus\etr\DigitaxController;
use App\Http\Controllers\Focus\invoice\InvoicesController;
use App\Http\Controllers\Focus\invoice_payment\InvoicePaymentsController;
use App\Http\Controllers\Focus\labour_allocation\LabourAllocationsController;
use App\Http\Controllers\Focus\lead\AgentLeadsController;
use App\Http\Controllers\Focus\lead\MediaBlocksController;
use App\Http\Controllers\Focus\mpesa_payment\StkPushsController;
use App\Http\Controllers\Focus\omniconvo\OmniController;
use App\Http\Controllers\Focus\promotions\ClientFeedbackController;
use App\Http\Controllers\Focus\promotions\PromoCodeReservationController;
use App\Http\Controllers\Focus\promotions\PromotionalCodeController;
use App\Http\Controllers\Focus\quote\QuoteBudgetController;
use App\Http\Controllers\Focus\quote\QuotesController;
use App\Http\Controllers\Focus\rfq\RfQController;
use App\Http\Controllers\Focus\sale_agent\SaleAgentsController;
use GuzzleHttp\Client;

// DigiTax Callback
Route::post('digitax/validation_cb', [DigitaxController::class, 'validationCb'])->name('digitax.validation_cb');

// Omniconvo Whatsapp Routes
Route::post('whatsapp_broadcasts/report', [MediaBlocksController::class, 'whatsappBroadcastReport'])->name('api.whatsapp_broadcast.report');
Route::post('whatsapp_broadcasts', [MediaBlocksController::class, 'whatsappBroadcastStore'])->name('api.whatsapp_broadcast.store');
Route::post('media_blocks/simple_text', [MediaBlocksController::class, 'store'])->name('api.media_blocks.create');
Route::post('media_blocks/delete', [MediaBlocksController::class, 'destroy'])->name('api.media_blocks.destroy');
Route::post('media_blocks/show', [MediaBlocksController::class, 'show'])->name('api.media_blocks.show');
Route::get('media_blocks', [MediaBlocksController::class, 'index'])->name('api.media_blocks.index');
// Omniconvo AI Chat-bot Routes
Route::post('read_chat', [OmniController::class, 'readChat'])->name('api.chatbot.read_chat');
Route::post('query_chats', [OmniController::class, 'queryChats'])->name('api.chatbot.query_chats');
Route::post('chat_transcript', [OmniController::class, 'getTranscript'])->name('api.chatbot.transcripts');
Route::post('webhooks/form_feedback/{company}', [OmniController::class, 'formFeedback']);
Route::post('webhooks/{company}', [OmniController::class, 'handle']);

// DigiTax E-Tims Callback
Route::post('invoices/etr_validation_cb', [InvoicesController::class, 'ETRValidationCb'])->name('api.invoices.etr_validation_cb');

// Bank Feeds
Route::post('bank_feeds/test', [BankFeedsController::class, 'store']);
Route::post('bank_feeds', [BankFeedsController::class, 'store']);

// AI agent leads webhook url
Route::post('agent_leads/store', [AgentLeadsController::class, 'store']);
Route::post('agent_leads/sms_callback', [AgentLeadsController::class, 'sms_callback']);
// Route::post('agent_leads/sms_failed_callback', [AgentLeadsController::class, 'sms_failed_callback']);
//https://9d2f-197-248-216-91.ngrok-free.app/api/agent_leads/sms_callback

Route::get('daily-business-metrics/{dbmUuid}', [DailyBusinessMetricController::class, 'index'])->name('daily-business-metrics');
Route::get('employee_summary_report/{user_id}/{month}/{year}', [LabourAllocationsController::class, 'employee_summary_report'])->name('employee_summary_report');
Route::get('kpi_summary_report/{user_id}/{month}/{year}/{financial_year_id}', [EmployeeDailyLogController::class, 'kpi_summary_report'])->name('kpi_summary_report');
Route::get('payment_received/{invoice_payment_id}/{token}', [InvoicePaymentsController::class, 'payment_received'])->name('payment_received');
Route::get('project_budget/store_list/{quote_id}/{token}', [QuoteBudgetController::class, 'store_list'])->name('project_budget.store_list');
Route::get('project_budget/technician_list/{quote_id}/{token}', [QuoteBudgetController::class, 'technician_list'])->name('project_budget.technician_list');
Route::get('invoice/{invoice_id}/{token}', [InvoicesController::class, 'invoicePDF'])->name('invoice');
Route::get('invoice_print/{invoice_id}/{token}', [InvoicesController::class, 'invoicePDF'])->name('invoice_print');
Route::get('print_quotation/{quote_id}/{token}', [QuotesController::class, 'quote_generate'])->name('print_quotation');
Route::get('rfq_link/{rfq_id}/{supplier_id}/{token}', [RfQController::class, 'rfq_generate'])->name('rfq_link');

Route::get('/{prefix}/{promoCode}/{uuid}', [PromoCodeReservationController::class, 'promoCodeLink'])->name('promo-code-link');

Route::get('reserve-referral-promo-code/{uuid}', [PromoCodeReservationController::class, 'createReferralReservation'])->name('reserve-referral-promo-code');
Route::post('save-referral-promo-code', [PromoCodeReservationController::class, 'reserveForReferral'])->name('save-referral-promo-code');

Route::get('submit-client-feedback', [ClientFeedbackController::class, 'create'])->name('submit-client-feedback');
Route::post('save-client-feedback', [ClientFeedbackController::class, 'store'])->name('save-client-feedback');

Route::get('generate-promo-code-banner/{uuid}', [PromoCodeReservationController::class, 'generatePromoCodeBanner'])->name('generate-promo-code-banner');

Route::get('contact-promo-business/{companyId}/{reservationUuid}', [PromoCodeReservationController::class, 'contactPromoBusiness'])->name('contact-promo-business');

Route::get('contact-us/{reservationUuid}', [PromoCodeReservationController::class, 'contactUs'])->name('contact-us');
Route::get('self_enroll', [PromotionalCodeController::class, 'self_enroll'])->name('self_enroll');
Route::post('self_enroll/store', [PromoCodeReservationController::class, 'storeForThirdParty'])->name('self_enroll.store');
Route::get('promotions', [PromotionalCodeController::class, 'get_promos'])->name('promotions');


Route::prefix('agents')->group(function () {
    Route::post('/register', [SaleAgentsController::class, 'register']);
    Route::post('/{uuid}/request-otp', [SaleAgentsController::class, 'requestOtp']);
    Route::post('/{uuid}/verify-otp', [SaleAgentsController::class, 'verifyOtp']);
    Route::patch('/{uuid}/profile', [SaleAgentsController::class, 'updateProfile']);
    Route::post('/{uuid}/documents', [SaleAgentsController::class, 'uploadCv']);
    Route::get('/{uuid}', [SaleAgentsController::class, 'show']);
    Route::get('/code/{public_code}', [SaleAgentsController::class, 'showByCode']); // 👈 public lookup
    Route::get('/code/{public_code}/resolve', [SaleAgentsController::class, 'resolveByCode']);
});
Route::patch('/agents/{uuid}/core', [SaleAgentsController::class, 'updateCore']);
Route::post('/otp/request', [SaleAgentsController::class, 'requestOtpForPhone']);

Route::prefix('daily-report')->group(function () {
    // Route::get('/pdf', [DailyBusinessMetricController::class, 'dailyReportPdf']);   // Existing PDF export
    Route::get('/json', [DailyBusinessMetricController::class, 'dailyReportJson'])->name('daily_report_json'); // New JSON endpoint
    // Route::get('/csv', [DailyBusinessMetricController::class, 'dailyReportCsv']);   // New CSV endpoint
});
Route::post('dbm_json_report', [DailyBusinessMetricController::class, 'dbmJsonReport'])->name('dbm_json_report');
//Mpesa
Route::post('mpesa_payment/stkpush', [StkPushsController::class, 'stkPush']);       // initiate
Route::post('mpesa_payment/callback', [StkPushsController::class, 'callback']);     // callback from Safaricom
Route::get('mpesa_payment/status/{checkoutRequestID}', [StkPushsController::class, 'status']); // optional status probe