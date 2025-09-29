<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Access\Permission\Permission;
use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\banktransfer\Banktransfer;
use App\Models\billpayment\Billpayment;
use App\Models\boq\BoQ;
use App\Models\boq_valuation\BoQValuation;
use App\Models\branch\Branch;
use DateInterval;
use DateTime;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

// ==== import EVERY model you reference in the payload ====
// Adjust namespaces to match your app structure.
use App\Models\Company\Company;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\customer_complain\CustomerComplain;
use App\Models\dailyBusinessMetric\DailyBusinessMetric;
use App\Models\department\Department;
use App\Models\documentManager\DocumentManager;
use App\Models\employeeDailyLog\EmployeeDailyLog;
use App\Models\employeeDailyLog\EmployeeTasks;
use App\Models\environmentalTracking\EnvironmentalTracking;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\health_and_safety\HealthAndSafetyTracking;
use App\Models\hrm\Hrm;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\job_valuation\JobValuation;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\lead\AgentLead;
use App\Models\lead\Lead;
use App\Models\lead\LeadSource;
use App\Models\lead\OmniChat;
use App\Models\leave\Leave;
use App\Models\leave_category\LeaveCategory;
use App\Models\misc\Misc;
use App\Models\product\ProductVariation;
use App\Models\project\Budget;
use App\Models\project\Project;
use App\Models\purchase\Purchase;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\quality_tracking\QualityTracking;
use App\Models\quote\Quote;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsCallback;
use App\Models\sms_response\SmsResponse;
use App\Models\supplier\Supplier;
use App\Models\tenant\GraceDaysRequest;
use App\Models\tenant\Tenant;
use App\Models\tenant\TenantActivation;
use App\Models\tenant\TenantDeactivation;
use App\Models\tenant\TenantLoyaltyPointsRedemption;
use App\Models\tender\Tender;
use App\Models\transaction\Transaction;
use Illuminate\Support\Facades\Storage;

trait DbmPayloadTrait
{
    /**
     * Build the full payload (moved out of controller).
     * EXACT copy of your logic, converted to PHP 7 anonymous functions for max compatibility.
     */
    protected function buildDbmPayload($dbm, DateTime $dateToday)
    {
        $now = new DateTime();

        // ---- AI Agent Summary ----
        $todayFmt = (clone $dateToday)->format('Y-m-d');

        // Cashbook balance Summary
        $payload['cashbookBalance'] = [
            [date('Y-m-d'), date('Y-m-d')], // today
            [date('Y-m-d', strtotime('yesterday')), date('Y-m-d', strtotime('yesterday'))], // yesterday
            [date('Y-m-d', strtotime('yesterday -7 days')), date('Y-m-d', strtotime('yesterday'))], // startWeek
            [date('Y-m-d', strtotime('yesterday -30 days')), date('Y-m-d', strtotime('yesterday'))], // startMonth
        ];
        foreach ($payload['cashbookBalance'] as $key => $value) {
            $trans = Transaction::whereBetween('tr_date', [$value])
                ->whereHas('account', function ($q) {
                    $q->whereHas('account_type_detail', fn($q) => $q->whereIn('system_rel', ['bank', 'cash']));
                })
                ->selectRaw('SUM(debit) debit, SUM(credit) credit, SUM(debit-credit) balance')
                ->first();
            $payload['cashbookBalance'][$key][] = $trans->debit ?? 0;
            $payload['cashbookBalance'][$key][] = $trans->credit ?? 0;
            $payload['cashbookBalance'][$key][] = $trans->balance ?? 0;
        }

        $payload['agentLeadsCount'] = [
            'totalCount'     => AgentLead::whereDate('created_at', $todayFmt)->count(),
            'facebookCount'  => AgentLead::whereHas('omniChat', function ($q) {
                $q->where('user_type', 'facebook');
            })->whereDate('created_at', $todayFmt)->count(),
            'whatsappCount'  => AgentLead::whereHas('omniChat', function ($q) {
                $q->where('user_type', 'whatsapp');
            })->whereDate('created_at', $todayFmt)->count(),
            'websiteCount'   => AgentLead::whereHas('omniChat', function ($q) {
                $q->where('user_type', 'website');
            })->whereDate('created_at', $todayFmt)->count(),
            'instagramCount' => AgentLead::whereHas('omniChat', function ($q) {
                $q->where('user_type', 'instagram');
            })->whereDate('created_at', $todayFmt)->count(),
        ];

        $payload['agentChatCount'] = [
            'totalCount'     => OmniChat::whereIn('user_type', ['facebook', 'whatsapp', 'website', 'instagram'])->whereHas('lastMessage', function ($q) use ($todayFmt) {
                $q->whereDate('created_at', $todayFmt);
            })->count(),
            'facebookCount'  => OmniChat::where('user_type', 'facebook')->whereHas('lastMessage', function ($q) use ($todayFmt) {
                $q->whereDate('created_at', $todayFmt);
            })->count(),
            'whatsappCount'  => OmniChat::where('user_type', 'whatsapp')->whereHas('lastMessage', function ($q) use ($todayFmt) {
                $q->whereDate('created_at', $todayFmt);
            })->count(),
            'websiteCount'   => OmniChat::where('user_type', 'website')->whereHas('lastMessage', function ($q) use ($todayFmt) {
                $q->whereDate('created_at', $todayFmt);
            })->count(),
            'instagramCount' => OmniChat::where('user_type', 'instagram')->whereHas('lastMessage', function ($q) use ($todayFmt) {
                $q->whereDate('created_at', $todayFmt);
            })->count(),
        ];

        // ---- Gross profit summary (class using this trait may override getProjectGrossProfitData) ----
        $payload['grossProfit']               = $this->getProjectGrossProfitData($dbm->ins, (clone $dateToday)->format('Y-m-d'));
        $payload['totalGrossProfitIncome']    = $payload['grossProfit']->pluck('income')->sum();
        $payload['totalGrossProfitExpense']   = $payload['grossProfit']->pluck('expense')->sum();
        $payload['totalGrossProfitProfit']    = $payload['grossProfit']->pluck('gross_profit')->sum();

        // ---- Tenants within 7 days of cutoff ----
        $payload['7DayTenants'] = Tenant::withoutGlobalScopes()
            ->where('billing_status', 'active')
            ->get()
            ->filter(function ($tenant) use ($now) {
                $billingDate = new DateTime($tenant->billing_date);
                $graceDays   = (int) $tenant->grace_days;
                $dueDate     = $billingDate->modify("+{$graceDays} days");
                return $now->diff($dueDate)->days <= 7 && $now <= $dueDate;
            })
            ->map(function ($t) {
                $cutoffDate = (new DateTime($t->billing_date))->add(new DateInterval('P' . $t->grace_days . 'D'))->format('d/m/Y H:i');
                return [
                    'name'        => $t->cname,
                    'cutoff_date' => empty($t->billing_date) ? 'Not Set' : $cutoffDate,
                ];
            });
        $payload['7DayTenantsCount'] = count($payload['7DayTenants']);

        $payload['tenantsActive'] = Tenant::withoutGlobalScopes()
            ->orderBy('cname')
            ->where('status', 'Active')
            ->where('billing_status', '!=', 'onboarding')
            ->latest()->get()
            ->map(function ($t) {
                return [
                    'name'         => $t->cname,
                    'billing_date' => (new DateTime($t->billing_date))->format('d/m/Y H:i'),
                ];
            });
        $payload['tenantsActiveCount'] = count($payload['tenantsActive']);

        $payload['tenantsSuspended'] = Tenant::withoutGlobalScopes()
            ->orderBy('cname')
            ->where('status', 'Suspended')
            ->latest()->get()
            ->map(function ($t) {
                return [
                    'name'         => $t->cname,
                    'billing_date' => (new DateTime($t->billing_date))->format('d/m/Y H:i'),
                ];
            });
        $payload['tenantsSuspendedCount'] = count($payload['tenantsSuspended']);

        $payload['tenantsOnboarding'] = Tenant::withoutGlobalScopes()
            ->orderBy('cname')
            ->where('billing_status', 'onboarding')
            ->latest()->get()
            ->map(function ($t) {
                return [
                    'name'         => $t->cname,
                    'billing_date' => (new DateTime($t->billing_date))->format('d/m/Y H:i'),
                ];
            });
        $payload['tenantsOnboardingCount'] = count($payload['tenantsOnboarding']);

        $payload['tenantsActivated'] = TenantActivation::withoutGlobalScopes()
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->groupBy('tenant_id')->latest()->get()
            ->map(function ($t) {
                $tenant = Tenant::withoutGlobalScopes()->find($t->tenant_id);
                return [
                    'name' => optional($tenant)->cname,
                    'time' => (new DateTime($t->created_at))->format('d/m/Y H:i'),
                ];
            });
        $payload['tenantsActivatedCount'] = count($payload['tenantsActivated']);

        $payload['tenantsDeactivated'] = TenantDeactivation::withoutGlobalScopes()
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->groupBy('tenant_id')->latest()->get()
            ->map(function ($t) {
                $tenant = Tenant::withoutGlobalScopes()->find($t->tenant_id);
                return [
                    'name' => optional($tenant)->cname,
                    'time' => (new DateTime($t->created_at))->format('d/m/Y H:i'),
                ];
            });
        $payload['tenantsDeactivatedCount'] = count($payload['tenantsDeactivated']);

        $payload['tenantsLoyaltyRedemptions'] = TenantLoyaltyPointsRedemption::withoutGlobalScopes()
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($t) {
                $tenant = Tenant::withoutGlobalScopes()->find($t->tenant_id);
                return [
                    'name'   => optional($tenant)->cname,
                    'points' => $t->points,
                    'days'   => $t->days,
                ];
            });
        $payload['totalRedeemedLoyaltyPoints'] = $payload['tenantsLoyaltyRedemptions']->pluck('points')->sum();
        $payload['totalRedeemedLoyaltyDays']   = $payload['tenantsLoyaltyRedemptions']->pluck('days')->sum();

        $payload['tenantsGraceRequests'] = GraceDaysRequest::withoutGlobalScopes()
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($t) {
                $tenant = Tenant::withoutGlobalScopes()->find($t->tenant_id);
                return [
                    'name' => optional($tenant)->cname,
                    'days' => $t->days,
                ];
            });
        $payload['totalRedeemedGraceDays'] = $payload['tenantsGraceRequests']
            ->pluck('days')->filter(function ($d) {
                return is_numeric($d);
            })->map(function ($d) {
                return (float) $d;
            })->sum();

        // ---- Leave applications ----
        $payload['leaveApplications'] = Leave::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($l) {
                $employee = User::find($l->employee_id);
                $category = LeaveCategory::withoutGlobalScopes()->find($l->leave_category_id);
                $start    = new DateTime($l->start_date);
                $end      = new DateTime($l->end_date);
                $duration = $end->diff($start)->d;
                return [
                    'employee'        => optional($employee)->first_name . ' ' . optional($employee)->last_name,
                    'category'        => optional($category)->title,
                    'submission_date' => (new DateTime($l->created_at))->format('Y-m-d'),
                    'reason'          => $l->reason,
                    'duration'        => $l->qty . ($l->qty > 1 ? ' days' : ' day'),
                    'start_date'      => (new DateTime($l->start_date))->format('Y-m-d'),
                    'end_date'        => (new DateTime($l->end_date))->format('Y-m-d'),
                    'status'          => strtoupper($l->status),
                ];
            });

        // ---- H&S / Quality / Environmental ----
        $payload['healthAndSafety'] = HealthAndSafetyTracking::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($h) {
                $customer = Customer::withoutGlobalScopes()->find($h->customer_id);
                $project  = Project::withoutGlobalScopes()->find($h->project_id);
                $branch   = Branch::withoutGlobalScopes()->find($h->branch_id);
                $involved = User::whereIn('id', json_decode($h->employee))->get()
                    ->map(function ($u) {
                        return optional($u)->first_name . ' ' . optional($u)->last_name;
                    });
                $involvedHtml = '';
                foreach ($involved as $item) $involvedHtml .= '<span>' . $item . ', </span><br>';
                return [
                    'date'          => (new DateTime($h->date))->format('Y-m-d'),
                    'project'       => optional($project)->name,
                    'customer'      => optional($customer)->company,
                    'branch'        => optional($branch)->name,
                    'involved'      => $involvedHtml,
                    'incident_desc' => $h->incident_desc,
                    'cause'         => $h->route_course,
                    'status'        => $h->status === 'first-aid-case' ? 'First Aid Case' : 'Lost Work Day',
                    'timing'        => $h->timing . ($h->timing > 1 ? ' days' : ' day'),
                ];
            });

        $payload['qualityTracking'] = QualityTracking::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($qt) {
                $customer = Customer::withoutGlobalScopes()->find($qt->customer_id);
                $project  = Project::withoutGlobalScopes()->find($qt->project_id);
                $branch   = Branch::withoutGlobalScopes()->find($qt->branch_id);
                $involved = User::whereIn('id', json_decode($qt->employee))->get()
                    ->map(function ($u) {
                        return optional($u)->first_name . ' ' . optional($u)->last_name;
                    });
                $involvedHtml = '';
                foreach ($involved as $item) $involvedHtml .= '<span>' . $item . ', </span><br>';
                return [
                    'date'          => (new DateTime($qt->date))->format('Y-m-d'),
                    'project'       => optional($project)->name,
                    'customer'      => optional($customer)->company,
                    'branch'        => optional($branch)->name,
                    'involved'      => $involvedHtml,
                    'incident_desc' => $qt->incident_desc,
                    'cause'         => $qt->route_course,
                    'status'        => $qt->status === 'first-aid-case' ? 'First Aid Case' : 'Lost Work Day',
                    'timing'        => $qt->timing . ($qt->timing > 1 ? ' days' : ' day'),
                ];
            });

        $payload['environmentalTracking'] = EnvironmentalTracking::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($qt) {
                $customer = Customer::withoutGlobalScopes()->find($qt->customer_id);
                $project  = Project::withoutGlobalScopes()->find($qt->project_id);
                $branch   = Branch::withoutGlobalScopes()->find($qt->branch_id);
                $involved = User::whereIn('id', json_decode($qt->employee))->get()
                    ->map(function ($u) {
                        return optional($u)->first_name . ' ' . optional($u)->last_name;
                    });
                $involvedHtml = '';
                foreach ($involved as $item) $involvedHtml .= '<span>' . $item . ', </span><br>';
                return [
                    'date'          => (new DateTime($qt->date))->format('Y-m-d'),
                    'project'       => optional($project)->name,
                    'customer'      => optional($customer)->company,
                    'branch'        => optional($branch)->name,
                    'involved'      => $involvedHtml,
                    'incident_desc' => $qt->incident_desc,
                    'cause'         => $qt->route_course,
                    'status'        => $qt->status === 'first-aid-case' ? 'First Aid Case' : 'Lost Work Day',
                    'timing'        => $qt->timing . ($qt->timing > 1 ? ' days' : ' day'),
                ];
            });

        // ---- Document Manager ----
        $payload['documentManager'] = DocumentManager::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)
                    ->orWhereDate('updated_at', $dateToday)
                    ->orWhereDate('renewal_date', $dateToday)
                    ->orWhereDate('expiry_date', $dateToday);
            })
            ->latest()->get()
            ->map(function ($dm) {
                $responsible   = User::find($dm->responsible);
                $coResponsible = User::find($dm->co_responsible);
                return [
                    'name'            => $dm->name,
                    'document_type'   => $dm->name,
                    'status'          => $dm->status,
                    'responsible'     => optional($responsible)->first_name . ' ' . optional($responsible)->last_name,
                    'co_responsible'  => optional($coResponsible)->first_name . ' ' . optional($coResponsible)->last_name,
                    'issue_date'      => (new DateTime($dm->issue_date))->format('Y-m-d'),
                    'renewal_date'    => (new DateTime($dm->renewal_date))->format('Y-m-d'),
                    'expiry_date'     => (new DateTime($dm->expiry_date))->format('Y-m-d'),
                    'cost_of_renewal' => number_format($dm->cost_of_renewal, 2),
                    'alert_days'      => $dm->alert_days_before . ($dm->alert_days_before > 1 ? ' days' : ' day'),
                ];
            });

        // ---- Customer Complaints ----
        $payload['customerComplaints'] = CustomerComplain::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($cc) {
                $customer = Customer::withoutGlobalScopes()->find($cc->customer_id);
                $project  = Project::withoutGlobalScopes()->find($cc->project_id);
                $branch   = Branch::withoutGlobalScopes()->find($cc->branch_id);
                $involved = User::whereIn('id', json_decode($cc->employees))->get()
                    ->map(function ($u) {
                        return optional($u)->first_name . ' ' . optional($u)->last_name;
                    });
                $involvedHtml = '';
                foreach ($involved as $item) $involvedHtml .= '<span>' . $item . ', </span><br>';
                $solver = User::find($cc->solver_id);
                return [
                    'date'            => (new DateTime($cc->date))->format('Y-m-d'),
                    'project'         => optional($project)->name,
                    'customer'        => optional($customer)->company,
                    'complaint_type'  => $cc->type_of_complaint,
                    'status'          => strtoupper($cc->status),
                    'complain_to'     => $involvedHtml,
                    'issue_description' => $cc->issue_description,
                    'initial_scale'   => $cc->initial_scale,
                    'solver'          => optional($solver)->first_name . ' ' . optional($solver)->last_name,
                    'current_scale'   => $cc->current_scale,
                ];
            });

        // ---- Sent SMS ----
        $payload['sentSms'] = SendSms::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($sms) {
                $time = $sms->delivery_type == 'now' ? $sms->created_at : $sms->scheduled_date;

                $response = SmsResponse::withoutGlobalScopes()->where('send_sms_id', $sms->id)->first();
                $callback = SmsCallback::withoutGlobalScopes()
                    ->where('reference', optional($response)->message_response_id)
                    ->where('delivery_status', 'DeliveredToTerminal')
                    ->first();

                $status = $callback ? 'Sent' : 'Not Sent';

                return [
                    'content'    => $sms->subject,
                    'type'       => strtoupper($sms->message_type),
                    'delivery'   => strtoupper($sms->delivery_type),
                    'status'     => $status,
                    'time_sent'  => (new DateTime($time))->format('jS M Y H:i'),
                    'created'    => (new DateTime($sms->created_at))->format('jS M Y H:i'),
                    'cost'       => $sms->total_cost,
                ];
            });

        $payload['sentSmsTotal'] = $payload['sentSms']
            ->filter(function ($sms) {
                return $sms['status'] === 'Sent';
            })
            ->pluck('cost')->sum();

        // ---- Bill Payments ----
        $payload['billPayments'] = Billpayment::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($bp) {
                $supplier = Supplier::withoutGlobalScopes()->find($bp->supplier_id);
                $account  = Account::withoutGlobalScopes()->find($bp->account_id);

                $utilityBills = DB::table('utility_bills')
                    ->join('bill_payment_items', 'utility_bills.id', '=', 'bill_payment_items.bill_id')
                    ->where('bill_payment_items.bill_payment_id', $bp->id)
                    ->select('utility_bills.*')
                    ->get();

                $billNos = [];
                foreach ($utilityBills as $bill) {
                    $billNos[] = gen4tid("BILL-", $bill->tid);
                }
                $billNos = implode(', ', $billNos);

                $dpNos = [];
                foreach ($utilityBills as $bill) {
                    $purch = Purchase::withoutGlobalScopes()->find($bill->purchase_id);
                    if ($purch) {
                        $dpNos[] = gen4tid("DP-", optional($purch)->tid);
                    }
                }
                $dpNos = implode(', ', $dpNos);

                return [
                    'tid'        => gen4tid('RMT-', $bp->tid),
                    'note'       => $bp->note,
                    'supplier'   => optional($supplier)->name,
                    'paid_from'  => optional($account)->holder,
                    'date'       => (new DateTime($bp->date))->format('Y-m-d'),
                    'mode'       => ucfirst($bp->payment_mode),
                    'reference'  => $bp->reference,
                    'bill_no'    => $billNos,
                    'dp_no'      => $dpNos,
                    'amount'     => $bp->amount,
                    'unallocated' => $bp->amount - $bp->allocate_ttl,
                ];
            });

        $payload['billPaymentsTotalAmount']      = $payload['billPayments']->pluck('amount')->sum();
        $payload['billPaymentsTotalUnallocated'] = $payload['billPayments']->pluck('unallocated')->sum();

        // ---- Invoices ----
        $payload['invoices'] = Invoice::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($inv) {
                $customer = Customer::withoutGlobalScopes()->find($inv->customer_id);
                $currency = Currency::withoutGlobalScopes()->find($inv->currency_id);
                return [
                    'tid'          => gen4tid('INV-', $inv->tid),
                    'customer'     => optional($customer)->company,
                    'status'       => strtoupper($inv->status),
                    'cu_invoice_no' => $inv->cu_invoice_no ?? 'N/A',
                    'date'         => (new DateTime($inv->invoicedate))->format('Y-m-d'),
                    'due_date'     => (new DateTime($inv->invoiceduedate))->format('Y-m-d'),
                    'currency'     => optional($currency)->code,
                    'total'        => $inv->total,
                    'tax'          => $inv->tax,
                ];
            });

        $payload['invoicesTotal']   = $payload['invoices']->pluck('total')->sum();
        $payload['invoicesTotalTax'] = $payload['invoices']->pluck('tax')->sum();

        // ---- Invoice Payments ----
        $payload['invoicePayments'] = InvoicePayment::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($payment) {
                $account  = Account::withoutGlobalScopes()->find($payment->account_id);
                $customer = Customer::withoutGlobalScopes()->find($payment->customer_id);
                $currency = Currency::withoutGlobalScopes()->find($payment->currency_id);
                $creator  = User::withoutGlobalScopes()->find($payment->user_id);
                return [
                    'tid'            => gen4tid('PMT-', $payment->tid),
                    'date'           => (new DateTime($payment->invoicedate))->format('Y-m-d'),
                    'customer'       => optional($customer)->company,
                    'note'           => $payment->note,
                    'creator'        => optional($creator)->first_name . ' ' . optional($creator)->last_name,
                    'account'        => optional($account)->holder,
                    'reference'      => $payment->reference ?? 'N/A',
                    'payment_mode'   => strtoupper($payment->payment_mode),
                    'payment_type'   => strtoupper(str_replace('_', ' ', $payment->payment_type)),
                    'currency'       => optional($currency)->code,
                    'amount'         => $payment->amount,
                    'wh_vat_amount'  => $payment->wh_vat_amount,
                    'wh_tax_amount'  => $payment->wh_tax_amount,
                ];
            });

        $payload['invoicePaymentsTotal']    = $payload['invoicePayments']->pluck('amount')->sum();
        $payload['invoicePaymentsTotalWhVat'] = $payload['invoicePayments']->pluck('wh_vat_amount')->sum();
        $payload['invoicePaymentsTotalWhTax'] = $payload['invoicePayments']->pluck('wh_tax_amount')->sum();

        // ---- Purchases ----
        $payload['purchases'] = Purchase::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($p) {
                $supplier = Supplier::withoutGlobalScopes()->find($p->supplier_id);
                return [
                    'tid'         => gen4tid('DP-', $p->tid),
                    'note'        => $p->note,
                    'supplier'    => optional($supplier)->name,
                    'status'      => strtoupper($p->status),
                    'cu_invoice_no' => $p->cu_invoice_no ?? 'N/A',
                    'date'        => (new DateTime($p->invoicedate))->format('Y-m-d'),
                    'due_date'    => (new DateTime($p->invoiceduedate))->format('Y-m-d'),
                    'total'       => $p->grandttl,
                    'tax'         => $p->grandtax,
                ];
            });

        $payload['purchasesTotal']   = $payload['purchases']->pluck('total')->sum();
        $payload['purchasesTotalTax'] = $payload['purchases']->pluck('tax')->sum();

        // ---- Purchase Orders ----
        $payload['purchase_orders'] = Purchaseorder::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($pO) {
                $supplier = Supplier::withoutGlobalScopes()->find($pO->supplier_id);
                $currency = Currency::withoutGlobalScopes()->find($pO->currency_id);
                return [
                    'tid'      => gen4tid('PO-', $pO->tid),
                    'note'     => $pO->note,
                    'supplier' => optional($supplier)->name,
                    'status'   => strtoupper($pO->status),
                    'date'     => (new DateTime($pO->date))->format('Y-m-d'),
                    'due_date' => (new DateTime($pO->due_date))->format('Y-m-d'),
                    'currency' => optional($currency)->code,
                    'total'    => $pO->grandttl,
                    'tax'      => $pO->grandtax,
                    'paid'     => $pO->paidttl,
                ];
            });

        $payload['purchaseOrdersTotal']     = $payload['purchase_orders']->pluck('total')->sum();
        $payload['purchaseOrdersTotalPaid'] = $payload['purchase_orders']->pluck('paid')->sum();
        $payload['purchaseOrdersTotalTax']  = $payload['purchase_orders']->pluck('tax')->sum();

        // ---- Stock Alerts ----
        $payload['stock_alert'] = ProductVariation::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->orderBy('name')
            ->whereDate('updated_at', $dateToday)
            ->whereRaw('qty < alert')
            ->get()
            ->map(function ($prod) {
                return [
                    'code'          => $prod->code,
                    'name'          => $prod->name,
                    'price'         => number_format($prod->price, 2),
                    'selling_price' => number_format($prod->selling_price, 2),
                    'fifo_cost'     => number_format($prod->fifo_cost, 2),
                    'qty'           => number_format($prod->qty, 2),
                    'alert'         => number_format($prod->alert, 2),
                    'moq'           => number_format($prod->moq, 2),
                ];
            });

        // ---- Quotes ----
        $payload['quotes'] = Quote::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($qt) {
                $customer = Customer::withoutGlobalScopes()->find($qt->customer_id);
                $lead     = Lead::withoutGlobalScopes()->find($qt->lead_id);
                $branch   = Branch::withoutGlobalScopes()->find($qt->branch_id);
                $currency = Currency::withoutGlobalScopes()->find($qt->currency_id);
                $creator  = User::withoutGlobalScopes()->find($qt->user_id);
                return [
                    'tid'         => gen4tid($qt->bank_id ? 'PI-' : 'QT-', $qt->tid),
                    'customer'    => empty($customer) ? optional($lead)->client_name : optional($customer)->company,
                    'branch'      => empty($customer) ? 'N/A' : optional($branch)->name,
                    'notes'       => $qt->notes,
                    'status'      => strtoupper($qt->status),
                    'approved_by' => $qt->approved_by ?? 'N/A',
                    'date'        => (new DateTime($qt->date))->format('Y-m-d'),
                    'creator'     => optional($creator)->first_name . ' ' . optional($creator)->last_name,
                    'currency'    => optional($currency)->code,
                    'total'       => $qt->total,
                    'tax'         => $qt->tax,
                ];
            });

        $payload['quotesTotal']    = $payload['quotes']->pluck('total')->sum();
        $payload['quotesTotalTax'] = $payload['quotes']->pluck('tax')->sum();

        // ---- Quote Budgets ----
        $payload['quoteBudgets'] = Budget::withoutGlobalScopes()
            ->join('quotes', 'budgets.quote_id', '=', 'quotes.id')
            ->where('budgets.ins', $dbm->ins)
            ->where('quotes.status', 'approved')
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('budgets.created_at', $dateToday)->orWhereDate('budgets.updated_at', $dateToday);
            })
            ->orderBy('budgets.created_at')
            ->get()
            ->map(function ($b) {
                $quote    = Quote::withoutGlobalScopes()->find($b->quote_id);
                $customer = Customer::withoutGlobalScopes()->find(optional($quote)->customer_id);
                $lead     = Lead::withoutGlobalScopes()->find(optional($quote)->lead_id);
                $branch   = Branch::withoutGlobalScopes()->find(optional($quote)->branch_id);
                $currency = Currency::withoutGlobalScopes()->find(optional($quote)->currency_id);
                $creator  = User::find($b->user_id);

                $margin = bcmul(bcdiv($b->quote_total - $b->budget_total, $b->quote_total, 4), 100, 2);

                return [
                    'quote'        => gen4tid('QT-', $quote->tid),
                    'customer'     => empty($customer) ? optional($lead)->client_name : optional($customer)->company,
                    'branch'       => empty($customer) ? 'N/A' : optional($branch)->name,
                    'notes'        => optional($quote)->notes,
                    'creator'      => optional($creator)->first_name . ' ' . optional($creator)->last_name,
                    'currency'     => optional($currency)->code,
                    'quoted_value' => $b->quote_total,
                    'budget'       => $b->budget_total,
                    'margin'       => $margin,
                ];
            });

        $payload['quoteBudgetsQuotesTotal']  = $payload['quoteBudgets']->pluck('quoted_value')->sum();
        $payload['quoteBudgetsBudgetsTotal'] = $payload['quoteBudgets']->pluck('budget')->sum();

        // ---- Birthdays ----
        $currentDay   = (clone $dateToday)->format('d');
        $currentMonth = (clone $dateToday)->format('m');
        $payload['birthdays'] = User::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where('status', 1)
            ->join('hrm_metas', 'users.id', '=', 'hrm_metas.user_id')
            ->whereDay('hrm_metas.dob', $currentDay)
            ->whereMonth('hrm_metas.dob', $currentMonth)
            ->orderBy('users.first_name')
            ->get()
            ->map(function ($e) {
                $department = Department::withoutGlobalScopes()->find($e->department_id);
                $dob = new DateTime($e->dob);
                $now = new DateTime();
                $age = $now->diff($dob)->y;
                return [
                    'name'       => $e->first_name . ' ' . $e->last_name,
                    'tid'        => $e->employee_no,
                    'position'   => $e->position,
                    'department' => optional($department)->name,
                    'age'        => $age,
                ];
            });

        // ---- 7-day charts / EDL ----
        $payload['sevenDayLabourHours']       = $this->get7DaysLabourMetrics($dbm->ins, $dbm->date);
        $payload['sevenDayLabourHoursTotal']  = array_sum($payload['sevenDayLabourHours']['hoursTotals']);

        $payload['sevenDaySalesExpenses']     = $this->get7DaysSalesExpensesMetrics($dbm->ins, $dbm->date);
        $payload['sdseSalesTotal']            = array_sum($payload['sevenDaySalesExpenses']['salesTotals']);
        $payload['sdseExpensesTotal']         = array_sum($payload['sevenDaySalesExpenses']['expensesTotals']);

        $payload['edlMetrics']                = $this->edlDashboard($dbm->ins, $dbm->date);

        // ---- Tickets (Leads) ----
        $payload['tickets'] = Lead::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($lead) {
                $customer   = Customer::withoutGlobalScopes()->find($lead->client_id);
                $branch     = Branch::withoutGlobalScopes()->find($lead->branch_id);
                $leadSource = LeadSource::withoutGlobalScopes()->find($lead->lead_source_id);
                $creator    = User::withoutGlobalScopes()->find($lead->user_id);
                return [
                    'tid'           => 'TKT-' . $lead->reference,
                    'title'         => $lead->title,
                    'status'        => $lead->status ? 'Won' : 'Pending',
                    'client_type'   => strtoupper($lead->client_status === 'customer' ? 'existing' : 'new'),
                    'customer'      => $lead->client_status == 'customer' ? optional($customer)->company : $lead->client_name,
                    'branch'        => optional($branch)->name,
                    'source'        => optional($leadSource)->name,
                    'client_contact' => $lead->client_status == 'customer' ? optional($customer)->phone : $lead->client_contact,
                    'client_email'  => $lead->client_status == 'customer' ? optional($customer)->email : $lead->client_email,
                    'creator'       => optional($creator)->first_name . ' ' . optional($creator)->last_name
                ];
            });

        // ---- Projects ----
        $payload['projects'] = Project::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($project) {
                $customer = Customer::withoutGlobalScopes()->find($project->customer_id);
                $branch   = Branch::withoutGlobalScopes()->find($project->branch_id);
                $status   = Misc::withoutGlobalScopes()->find($project->status);
                $quote    = Quote::withoutGlobalScopes()->find($project->main_quote_id);
                $creator  = User::find($project->user_id);
                return [
                    'tid'        => 'PRJ-' . $project->tid,
                    'title'      => $project->name,
                    'priority'   => strtoupper($project->priority),
                    'status'     => optional($status)->name,
                    'quote'      => 'QT-' . optional($quote)->tid,
                    'customer'   => optional($customer)->company,
                    'branch'     => optional($branch)->name,
                    'created_by' => optional($creator)->first_name . " " . optional($creator)->last_name,
                ];
            });

        // ---- GRNs ----
        $payload['goodsReceiveNotes'] = Goodsreceivenote::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($grn) {
                $supplier      = Supplier::withoutGlobalScopes()->find($grn->supplier_id);
                $currency      = Currency::withoutGlobalScopes()->find($grn->currency_id);
                $purchaseOrder = Purchaseorder::withoutGlobalScopes()->find($grn->purchaseorder_id);
                $creator       = User::find($grn->user_id);
                return [
                    'tid'          => 'GRN-' . $grn->tid,
                    'date'         => (new DateTime($grn->date))->format('jS M Y'),
                    'title'        => $grn->note,
                    'supplier'     => optional($supplier)->name,
                    'purchaseOrder' => 'PO-' . optional($purchaseOrder)->tid,
                    'deliveryNote' => $grn->dnote,
                    'invoiceNo'    => $grn->invoice_no,
                    'invoiceDate'  => $grn->invoice_date,
                    'createdBy'    => optional($creator)->first_name . " " . optional($creator)->last_name,
                    'currency'     => optional($currency)->code,
                    'total'        => $grn->total,
                    'tax'          => $grn->tax,
                ];
            });

        $payload['goodsReceiveNotesTotal']    = $payload['goodsReceiveNotes']->pluck('total')->sum();
        $payload['goodsReceiveNotesTotalTax'] = $payload['goodsReceiveNotes']->pluck('tax')->sum();

        // ---- Bank Transfers ----
        $payload['bankTransfers'] = Banktransfer::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($bt) {
                $account      = Account::withoutGlobalScopes()->find($bt->account_id);
                $debitAccount = Account::withoutGlobalScopes()->find($bt->debit_account_id);
                $creator      = User::find($bt->user_id);
                return [
                    'tid'         => 'BT-' . $bt->tid,
                    'date'        => (new DateTime($bt->transaction_date))->format('jS M Y'),
                    'method'      => $bt->method,
                    'refer_no'    => $bt->refer_no,
                    'account'     => optional($account)->holder,
                    'debitAccount' => optional($debitAccount)->holder,
                    'note'        => $bt->note,
                    'createdBy'   => optional($creator)->first_name . " " . optional($creator)->last_name,
                    'amount'      => $bt->amount,
                ];
            });
        $payload['bankTransfersTotal'] = $payload['bankTransfers']->pluck('amount')->sum();

        // ---- Job Valuations ----
        $payload['job_valuations'] = JobValuation::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($v) {
                $quote   = $v->quote()->withoutGlobalScopes()->first();
                $customer = $v->customer()->withoutGlobalScopes()->first();
                $branch  = $v->branch()->withoutGlobalScopes()->first();
                $invoice = $v->invoice()->withoutGlobalScopes()->first();
                $name = '';
                if ($customer) $name = $customer->company ?: $customer->name;
                if ($customer && $branch) $name .= " - {$branch->name}";
                $quote_tid = $quote ? ($quote->bank_id ? gen4tid('PI-', $quote->tid) : gen4tid('QT-', $quote->tid)) : '';
                return [
                    'tid'         => gen4tid('JV-', $v->tid),
                    'quote_tid'   => $quote_tid,
                    'customer'    => $name,
                    'note'        => $v->note,
                    'total'       => $quote ? $quote->subtotal : 0,
                    'subtotal'    => $v->valued_subtotal,
                    'balance'     => $v->balance,
                    'date'        => $v->date,
                    'invoice_tid' => $invoice ? gen4tid('INV-', $invoice->tid) : '',
                ];
            });

        // ---- BoQ Valuations ----
        $payload['boq_valuations'] = BoQValuation::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($v) {
                $boq      = BoQ::withoutGlobalScopes()->find($v->boq_id);
                $lead     = $boq ? $boq->lead()->withoutGlobalScopes()->first() : null;
                $customer = $lead ? $lead->customer()->withoutGlobalScopes()->first() : null;
                $branch   = $lead ? $lead->branch()->withoutGlobalScopes()->first() : null;
                $invoice  = $v->invoice()->withoutGlobalScopes()->first();

                $name = $customer ? ($customer->company ?: $customer->name) : '';
                if ($name && $branch && $branch->name) $name .= " - {$branch->name}";

                return [
                    'tid'         => gen4tid('BV-', $v->tid),
                    'boq_tid'     => $boq ? gen4tid('BoQ-', $boq->tid) : '',
                    'customer'    => $name,
                    'note'        => $v->note,
                    'total'       => $boq ? $boq->total_boq_amount : 0,
                    'subtotal'    => $v->valued_subtotal,
                    'balance'     => $v->balance,
                    'date'        => $v->date,
                    'invoice_tid' => $invoice ? gen4tid('INV-', $invoice->tid) : '',
                ];
            });

        // ---- Tenders ----
        $payload['tenders'] = Tender::withoutGlobalScopes()
            ->where('ins', $dbm->ins)
            ->where(function ($query) use ($dateToday) {
                $query->whereDate('created_at', $dateToday)->orWhereDate('updated_at', $dateToday);
            })
            ->latest()->get()
            ->map(function ($tender) {
                $lead        = $tender->lead()->withoutGlobalScopes()->first();
                $branch      = $lead ? $lead->branch()->withoutGlobalScopes()->first() : '';
                $clientname  = $lead ? $lead->client_name : '';
                $branch_name = $branch ? $branch->name : '';
                $client      = $tender->client()->withoutGlobalScopes()->first();
                if ($client) {
                    $c_branch    = $tender->branch()->withoutGlobalScopes()->first();
                    $clientname  = $client->company;
                    $branch_name = $c_branch ? $c_branch->name : '';
                }
                return [
                    'customer'        => trim($clientname . ' ' . $branch_name),
                    'ticket_tid'      => $lead ? gen4tid("TKT-", $lead->reference) : '',
                    'title'           => $tender->title,
                    'stages'          => ucfirst($tender->tender_stages),
                    'submission_date' => $tender->submission_date,
                    'site_visit_date' => $tender->site_visit_date,
                    'amount'          => numberFormat($tender->amount),
                    'bid_bond_amount' => numberFormat($tender->bid_bond_amount),
                ];
            });

        return $payload;
    }

    /**
     * Render the PDF (A3) using your existing Blade.
     * Returns raw PDF bytes.
     */
    protected function renderDbmPdf($dbm, DateTime $dateToday, array $payload)
    {
        $company   = Company::find($dbm->ins);
        $yesterday = (clone $dateToday)->sub(new DateInterval('P1D'))->format('jS F, Y');

        $htmlContent = View::make('focus.dailyBusinessMetrics.printDailyMetrics', [
            'payload'   => $payload,
            'company'   => $company,
            'yesterday' => $yesterday,
            'dateToday' => $dateToday,
            'dbm'       => $dbm,
        ])->render();

        $mpdf = new Mpdf([
            'format'        => 'A3',
            'margin_top'    => 10,
            'margin_bottom' => 20,
            'margin_left'   => 15,
            'margin_right'  => 15,
        ]);
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';
        $mpdf->SetAutoPageBreak(true, 10);
        $mpdf->WriteHTML($htmlContent);

        return $mpdf->Output('', 'S'); // string
    }

    /**
     * Used by payload above; override in the class if you already have it.
     * Default no-op returns an empty collection so the PDF still renders.
     */
    protected function getProjectGrossProfitData($ins, $ymdDate)
    {
        return collect();
    }

    // ===== Helpers moved from your controller =====

    protected function get7DaysLabourMetrics($ins, $dbmDate)
    {
        $hoursTotals = array_fill(0, 7, 0);
        $labourDates = array_fill(0, 7, 'N/A');

        for ($i = 1; $i <= 7; $i++) {
            $date = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');

            $labourAllocations = LabourAllocation::withoutGlobalScopes()
                ->where('ins', $ins)
                ->where('date', $date)
                ->pluck('hrs');

            foreach ($labourAllocations as $alloc) $hoursTotals[$i - 1] += $alloc;
            $labourDates[$i - 1] = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('jS M');
        }

        $labourDates  = array_reverse($labourDates);
        $hoursTotals  = array_reverse($hoursTotals);
        $startDate    = (new DateTime($dbmDate))->sub(new DateInterval('P7D'))->format('jS F');
        $endDate      = (new DateTime($dbmDate))->sub(new DateInterval('P1D'))->format('jS F');
        $chartTitle   = 'Daily Labour Hours from ' . $startDate . ' to ' . $endDate . ', ' . (new DateTime($dbmDate))->format('Y');

        return compact('hoursTotals', 'labourDates', 'chartTitle');
    }

    protected function get7DaysSalesExpensesMetrics($ins, $dbmDate)
    {
        $salesTotals = array_fill(0, 7, 0);
        $salesDates  = array_fill(0, 7, 'N/A');

        for ($i = 1; $i <= 7; $i++) {
            $date = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');
            $salesValues = Invoice::withoutGlobalScopes()
                ->where('ins', $ins)
                ->where('invoicedate', $date)
                ->pluck('total');
            foreach ($salesValues as $sale) $salesTotals[$i - 1] += $sale;
            $salesDates[$i - 1] = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('jS M');
        }

        $salesDates  = array_reverse($salesDates);
        $salesTotals = array_reverse($salesTotals);

        $expensesTotals = array_fill(0, 7, 0);
        $expensesDates  = array_fill(0, 7, 'N/A');

        for ($i = 1; $i <= 7; $i++) {
            $date = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');
            $expensesValues = Purchase::withoutGlobalScopes()
                ->where('ins', $ins)
                ->where('date', $date)
                ->pluck('grandttl');
            foreach ($expensesValues as $expense) $expensesTotals[$i - 1] += $expense;
            $expensesDates[$i - 1] = (new DateTime($dbmDate))->sub(new DateInterval('P' . $i . 'D'))->format('jS M');
        }

        $expensesDates  = array_reverse($expensesDates);
        $expensesTotals = array_reverse($expensesTotals);

        $startDate  = (new DateTime($dbmDate))->sub(new DateInterval('P7D'))->format('jS F');
        $endDate    = (new DateTime($dbmDate))->sub(new DateInterval('P1D'))->format('jS F');
        $chartTitle = 'Daily Sales and Expenses from ' . $startDate . ' to ' . $endDate . ', ' . (new DateTime($dbmDate))->format('Y');

        return compact('salesTotals', 'salesDates', 'expensesTotals', 'expensesDates', 'chartTitle');
    }

    protected function edlDashboard($ins, $dbmDate)
    {
        $today = (new DateTime($dbmDate))->format('Y-m-d');

        $filledToday = EmployeeDailyLog::withoutGlobalScopes()
            ->where('ins', $ins)
            ->where('date', $today)
            ->get()->count();

        $employees  = Hrm::withoutGlobalScopes()->where('ins', $ins)->get();
        $noOfLoggers = 0;
        foreach ($employees as $emp) {
            $user = User::where('id', $emp['id'])->first();
            $perm = Permission::where('name', 'create-daily-logs')->first();
            $permUser = PermissionUser::where('user_id', $user->id)->where('permission_id', $perm->id)->first();
            if ($permUser) $noOfLoggers++;
        }
        $notFilledToday = $noOfLoggers - $filledToday;

        $tasksLoggedToday = 0;
        $hoursLoggedToday = 0;
        $todayLogs = EmployeeDailyLog::withoutGlobalScopes()->where('ins', $ins)->where('date', $today)->get();

        foreach ($todayLogs as $log) {
            $edlTasks = EmployeeTasks::withoutGlobalScopes()
                ->where('ins', $ins)
                ->where('edl_number', $log['edl_number'])
                ->get();

            $tasksLoggedToday += $edlTasks->count();

            foreach ($edlTasks as $task) $hoursLoggedToday += $task['hours'];
        }

        $todayUnreviewedLogs = 0;
        foreach ($todayLogs as $log) {
            if (empty($log['rating']) && empty($log['remarks'])) $todayUnreviewedLogs++;
        }

        return compact('filledToday', 'notFilledToday', 'tasksLoggedToday', 'hoursLoggedToday', 'todayUnreviewedLogs');
    }

    public function dailyReportJson(Request $request)
    {
        $uuid     = $request->input('uuid');
        $download = (bool) $request->boolean('download');
        $save     = (bool) $request->boolean('save');

        $dbm = DailyBusinessMetric::withoutGlobalScopes()
            ->where('dbm_uuid', $uuid)
            ->firstOrFail();

        $dateToday = new \DateTime($dbm->date);
        $payload   = $this->buildDbmPayload($dbm, $dateToday);

        // ✅ Reuse
        $jsonPayload = $this->buildDbmJsonPayload($payload, $dateToday);
        $json        = $this->renderDbmJson($jsonPayload);

        if (!$download && !$save) {
            return response($json, 200, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        $safeDate = $dateToday->format('Y-m-d');
        $filename = "daily-report-{$uuid}-{$safeDate}.json";

        if ($download && !$save) {
            return response($json, 200, [
                'Content-Type'           => 'application/json; charset=utf-8',
                'Content-Disposition'    => 'attachment; filename="' . $filename . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        $path = "reports/{$filename}";
        Storage::put($path, $json, 'private');

        return Storage::download($path, $filename, [
            'Content-Type'           => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Daily Business Metric JSON report
     * */
    public function dbmJsonReport(Request $request)
    {
        // validation
        
        $dailyMetrics = DailyBusinessMetric::withoutGlobalScopes()
            ->where('ins', $request->scope_id)
            ->whereNotNull('report_json')
            ->whereBetween('date', [date('Y-m-d', strtotime('-1 weeks')), date('Y-m-d')])
            ->pluck('report_json');
        
        return response()->json($dailyMetrics);
    }


    /**
     * Build the JSON payload structure from a raw $payload and date.
     */
    public function buildDbmJsonPayload(array $payload, \DateTime $dateToday): array
    {
        return [
            "Tickets Created/Updated on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section provides a summary of all tickets (leads) created or updated on the specified date. It includes client details, status, and contact information, helping you track new and ongoing opportunities.",
                'records'     => $payload['tickets'] ?? []
            ],
            "Quotes & Proforma Invoices Processed/Updated on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section provides a summary of all Quotes & Proforma Invoices issued on the specified date. It includes customer details, quote amounts, and their current status, giving a comprehensive overview of quote activity.",
                'records'     => $payload['quotes'] ?? [],
                'totals'      => [
                    'total' => $payload['quotesTotal'] ?? 0,
                    'tax'   => $payload['quotesTotalTax'] ?? 0,
                ]
            ],
            "Projects Created/Updated on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section summarizes all projects created or updated on the specified date, including project titles, related quotes, customers, and responsible staff.",
                'records'     => $payload['projects'] ?? []
            ],
            "Gross Profit for Projects Ended on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section provides the gross profit analysis for projects that ended on the specified date. It highlights income, expenses, and resulting profit margins.",
                'records'     => $payload['grossProfit'] ?? [],
                'totals'      => [
                    'income'       => $payload['totalGrossProfitIncome'] ?? 0,
                    'expense'      => $payload['totalGrossProfitExpense'] ?? 0,
                    'gross_profit' => $payload['totalGrossProfitProfit'] ?? 0,
                ]
            ],
            "Invoices Processed/Updated on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section lists all invoices generated or updated on the specified date, including customer details, amounts, taxes, and due dates.",
                'records'     => $payload['invoices'] ?? [],
                'totals'      => [
                    'total' => $payload['invoicesTotal'] ?? 0,
                    'tax'   => $payload['invoicesTotalTax'] ?? 0,
                ]
            ],
            "Invoice Payments Processed/Updated on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section lists invoice payments processed or updated on the specified date, detailing payment methods, accounts, and amounts.",
                'records'     => $payload['invoicePayments'] ?? [],
                'totals'      => [
                    'amount' => $payload['invoicePaymentsTotal'] ?? 0,
                    'wh_vat' => $payload['invoicePaymentsTotalWhVat'] ?? 0,
                    'wh_tax' => $payload['invoicePaymentsTotalWhTax'] ?? 0,
                ]
            ],
            "Purchases Processed/Updated on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section provides an overview of all purchases created or updated on the specified date, including suppliers, amounts, and tax summaries.",
                'records'     => $payload['purchases'] ?? [],
                'totals'      => [
                    'total' => $payload['purchasesTotal'] ?? 0,
                    'tax'   => $payload['purchasesTotalTax'] ?? 0,
                ]
            ],
            "Purchase Orders Created/Updated on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section lists purchase orders created or updated on the specified date. It includes supplier, currency, totals, taxes, and amounts paid.",
                'records'     => $payload['purchase_orders'] ?? [],
                'totals'      => [
                    'total' => $payload['purchaseOrdersTotal'] ?? 0,
                    'paid'  => $payload['purchaseOrdersTotalPaid'] ?? 0,
                    'tax'   => $payload['purchaseOrdersTotalTax'] ?? 0,
                ]
            ],
            "GRNs Created/Updated on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section provides a summary of all Goods Receive Notes (GRNs) created or updated on the specified date.",
                'records'     => $payload['goodsReceiveNotes'] ?? [],
                'totals'      => [
                    'total' => $payload['goodsReceiveNotesTotal'] ?? 0,
                    'tax'   => $payload['goodsReceiveNotesTotalTax'] ?? 0,
                ]
            ],
            "Bank Transfers Created/Updated on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section lists bank transfers created or updated on the specified date, including debit/credit accounts, methods, and amounts.",
                'records'     => $payload['bankTransfers'] ?? [],
                'total'       => $payload['bankTransfersTotal'] ?? 0
            ],
            "Bill Payments Created/Updated on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section summarizes bill payments created or updated on the specified date, including supplier, paid accounts, and allocation details.",
                'records'     => $payload['billPayments'] ?? [],
                'totals'      => [
                    'amount'      => $payload['billPaymentsTotalAmount'] ?? 0,
                    'unallocated' => $payload['billPaymentsTotalUnallocated'] ?? 0,
                ]
            ],
            "Customer Complaints" => [
                'description' => "This section highlights all customer complaints received or updated on the specified date, including type, status, and resolution progress.",
                'records'     => $payload['customerComplaints'] ?? []
            ],
            "Leave Applications" => [
                'description' => "This section lists all employee leave applications submitted or updated on the specified date, including category, duration, and approval status.",
                'records'     => $payload['leaveApplications'] ?? []
            ],
            "Document Manager Updates" => [
                'description' => "This section provides updates from the document manager, showing documents issued, renewed, or expiring on the specified date.",
                'records'     => $payload['documentManager'] ?? []
            ],
            "H&S Tracking" => [
                'description' => "This section provides all Health & Safety records logged or updated on the specified date.",
                'records'     => $payload['healthAndSafety'] ?? []
            ],
            "Quality Tracking" => [
                'description' => "This section provides all Quality Tracking entries created or updated on the specified date.",
                'records'     => $payload['qualityTracking'] ?? []
            ],
            "Environmental Tracking" => [
                'description' => "This section provides all Environmental Tracking entries created or updated on the specified date.",
                'records'     => $payload['environmentalTracking'] ?? []
            ],
            "Sent SMS on {$dateToday->format('Y-m-d')}" => [
                'description' => "This section lists all SMS messages sent or scheduled on the specified date, including costs and delivery status.",
                'records'     => $payload['sentSms'] ?? [],
                'total'       => $payload['sentSmsTotal'] ?? 0
            ],
            "Cashbook Balance Summary" => [
                'description' => "This section summarizes the organization’s cashbook balances across multiple periods (today, yesterday, 7-day, 30-day).",
                'records'     => $payload['cashbookBalance'] ?? []
            ],
            "7 Day Tenants" => [
                'description' => "This section lists tenants within 7 days of cutoff, helping monitor potential service suspensions.",
                'records'     => $payload['7DayTenants'] ?? [],
                'count'       => $payload['7DayTenantsCount'] ?? 0
            ],
            "Birthdays Today" => [
                'description' => "This section lists employees celebrating birthdays today.",
                'records'     => $payload['birthdays'] ?? []
            ],
            "Stock Alerts" => [
                'description' => "This section highlights products whose stock levels have fallen below their defined alert thresholds.",
                'records'     => $payload['stock_alert'] ?? []
            ],
            "7 Day Labour Hours" => [
                'description' => "This section shows the labor hours recorded over the last 7 days, including totals.",
                'metrics'     => $payload['sevenDayLabourHours'] ?? [],
                'total'       => $payload['sevenDayLabourHoursTotal'] ?? 0
            ],
            "7 Day Sales & Expenses" => [
                'description' => "This section provides sales and expense totals over the last 7 days.",
                'metrics'     => $payload['sevenDaySalesExpenses'] ?? [],
                'sales'       => $payload['sdseSalesTotal'] ?? 0,
                'expenses'    => $payload['sdseExpensesTotal'] ?? 0,
            ],
            "EDL Metrics" => [
                'description' => "This section summarizes Engineering Daily Log (EDL) metrics captured for the specified date.",
                'records'     => $payload['edlMetrics'] ?? []
            ],
            "Job Valuations" => [
                'description' => "This section lists job valuations processed or updated on the specified date.",
                'records'     => $payload['job_valuations'] ?? []
            ],
            "BoQ Valuations" => [
                'description' => "This section lists Bill of Quantity (BoQ) valuations processed or updated on the specified date.",
                'records'     => $payload['boq_valuations'] ?? []
            ],
            "Tenders" => [
                'description' => "This section provides all tenders created or updated on the specified date, including submission and site visit dates, amounts, and bid bonds.",
                'records'     => $payload['tenders'] ?? []
            ],
        ];
    }

    /**
     * JSON formatter (pretty + UTF-8 safe).
     */
    protected function renderDbmJson(array $jsonPayload): string
    {
        return json_encode(
            $jsonPayload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) . PHP_EOL;
    }
}
