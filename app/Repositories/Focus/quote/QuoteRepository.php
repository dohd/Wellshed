<?php

namespace App\Repositories\Focus\quote;

use App\Models\items\QuoteItem;
use App\Models\items\VerifiedItem;

use App\Models\quote\Quote;
use App\Exceptions\GeneralException;
use App\Jobs\VerifyNotifyUsers;
use App\Models\Access\User\User;
use App\Models\customer\Customer;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use  App\Models\hrm\Hrm;
use App\Repositories\BaseRepository;

use App\Models\lead\Lead;
use App\Models\project\BudgetSkillset;
use App\Models\verifiedjcs\VerifiedJc;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\quote\EquipmentQuote;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\labour_allocation\LabourAllocationItem;
use App\Models\misc\Misc;
use App\Models\send_sms\SendSms;
use App\Models\stock_transfer\StockTransfer;
use App\Notifications\VerifyNotification;
use App\Repositories\Focus\general\RosesmsRepository;

/**
 * Class QuoteRepository.
 */
class QuoteRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Quote::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query()->with('currency');

        // filter for customer-login
        $customer_id = auth()->user()->customer_id;
        $q->when($customer_id, fn($q) => $q->where('customer_id', $customer_id)); 
        
        $q->when(request('classlist_id'), fn($q) => $q->where('classlist_id', request('classlist_id')));
        
        $q->when(request('page') == 'pi', fn($q) => $q->where('bank_id', '>', 0));
        $q->when(request('page') == 'qt', fn($q) => $q->where('bank_id', 0));
        
        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
        });

        $q->when(request('source_filter'), function ($q) {
            $q->whereHas('lead', function ($query) {
                $query->whereHas('LeadSource', function ($query) {
                    $query->where('id', request('source_filter'));
                });
            })->with(['lead' => function ($query) {
                $query->with('LeadSource');
            }]);
        });
        // client filter
        $q->when(request('client_id'), fn($q) => $q->where('customer_id', request('client_id')));

        $q->when(request('account_id'), fn($q) => $q->where('account_id', request('account_id')));
        
        // status criteria filter
        $status = true;
        if (request('status_filter')) {
            switch (request('status_filter')) {
                case 'Unapproved':
                    $q->whereNull('approved_by');
                    break;
                case 'Approved & Unbudgeted':
                    $q->whereNotNull('approved_by')->whereNull('project_quote_id');
                    break;
                case 'Budgeted & Unverified':
                    $q->whereNotNull('project_quote_id')->whereNull('verified_by');
                    break;
                case 'Budgeted, Expensed & Unverified':
                    $q->whereNotNull('project_quote_id')->whereNull('verified_by')
                        ->where(function($q) {
                            $q->whereHas('project', function($q) {
                                $q->whereHas('purchase_items')->orWhereHas('grn_items');
                            });
                            $q->orWhereHas('projectstock');
                    });
                    break;
                case 'Verified with LPO & Uninvoiced':
                    $q->whereNotNull('verified_by')->whereNotNull('lpo_id')->where('invoiced', 'No');
                    break;
                case 'Verified without LPO & Uninvoiced':
                    $q->whereNotNull('verified_by')->whereNull('lpo_id')->where('invoiced', 'No');
                    break;
                case 'Approved without LPO & Uninvoiced':
                    $q->whereNotNull('approved_by')->whereNull('lpo_id')->where('invoiced', 'No');
                    break;
                case 'Approved & Uninvoiced':
                    $q->whereNotNull('approved_by')->doesntHave('invoice_product');
                    break;                    
                case 'Invoiced & Due':
                    // quotes in due invoices
                    $q->whereHas('invoice_product', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('status', 'due');
                        });
                    });
                    break;
                case 'Invoiced & Partially Paid':
                    // quotes in partially paid invoices
                    $q->whereHas('invoice_product', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('status', 'partial');
                        });
                    });
                    break;
                case 'Invoiced & Paid':
                    // quotes in partially paid invoices
                    $q->whereHas('invoice_product', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('status', 'paid');
                        });
                    });
                    break;
                case 'Invoiced':
                    $q->whereHas('invoice_product')->orWhereHas('invoice_quote');
                    break;                    
                case 'Cancelled':
                    $status = false;
                    $q->where('status', 'cancelled');
                    break;
            }
        }
        $q->when($status, fn($q) => $q->where('status', '!=', 'cancelled'));

        // project filter
        $q->when(request('project_id'), function($q) {
            $q->whereHas('project', fn($q) => $q->where('projects.id', request('project_id')));
        });

        // Exclude terminated project quotes
        if (!request('project_id')) {
            // $exists = strpos(request('status_filter'), 'Invoiced') !== false;
            $q->where(function($q) {
                $q->whereDoesntHave('project')
                ->orwhereHas('project', function($q) {
                    $q->whereHas('misc', fn($q) => $q->where('name', '!=', 'Terminated'));
                });                    
            });
        }
        
        return $q->orderBy('id','desc');
    }

    /**
     * Verification Quotes
     */
    public function getForVerifyDataTable()
    {
        $q = $this->query();

        $q->where('status', '!=', 'cancelled');
        $q->doesntHave('job_valuations');

        $q->where(function($q) {
            // project quote has budget
            $q->whereHas('budget');
            // standard quote is approved
            $q->orWhere(fn($q) => $q->where(['quote_type' => 'standard', 'status' => 'approved']));
        });

        // customer and branch filter
        $q->when(request('customer_id'), fn($q) => $q->where('customer_id', request('customer_id')))
        ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')));
        
        // state filter
        if (request('verify_state')) $q->where('verified', request('verify_state'));

        // date filter
        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
        });
        
        // project stock filter
        $q->when(request('is_project_stock'), function($q) {
            $q->whereHas('project', function($q) {
                $q->whereHas('misc', fn($q) => $q->where('name', '!=', 'Completed'));
            });
        });

        // fallback search 
        $q->when(request('term'), function($q) {
            $terms = explode('-', request('term'));
            if (stripos(request('term'), 'QT') !== false) {
                $q->where('bank_id', 0)->where('tid', intval(@$terms[1]));
            } elseif (stripos(request('term'), 'PI') !== false) {
                $q->where('bank_id', '>', 0)->where('tid', intval(@$terms[1]));
            } elseif (stripos(request('term'), 'PRJ') !== false) {
                $q->whereHas('project', function($q) use($terms) {
                    $q->where('tid', intval(@$terms[1]));
                });
            } else {
                $q->where(function($q) {
                    $q->where('tid', request('term'))
                    ->orWhereHas('project', fn($q) => $q->where('tid', request('term')));
                });
            }
        });

        // limit results
        $q->limit(100)->orderBy('verification_date', 'DESC');
        
        return $q;
    }

    /**
     * Quotes pending invoicing
     */
    public function getForVerifyNotInvoicedDataTable()
    {
        $q = $this->query();
        
        // standard quote or budget project quote
        $q->where(fn($q) => $q->whereHas('budget')->orWhere('quote_type', 'standard'));

        // verified and uninvoiced quotes
        $q->where(['verified' => 'Yes', 'invoiced' => 'No'])->whereDoesntHave('invoice_product');
                
        $q->when(request('customer_id'), fn($q) => $q->where('customer_id', request('customer_id')));
        $q->when(request('lpo_number'), fn($q) => $q->where('lpo_id', request('lpo_number')));
        $q->when(request('project_id'), function ($q) {
            $q->whereIn('id', function($q) {
                $q->select('quote_id')->from('project_quotes')->where('project_id', request('project_id'));
            });
        });
        
        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'branch_id', 'date', 
            'total', 'bank_id', 'verified_total', 'lpo_id', 'project_quote_id', 'currency_id'
        ]);
    }
    
    
    /** Turn around time **/
    public function getForTurnAroundTime()
    {
        $q = $this->query()->with('currency')->whereNotNull('approved_by');

        // filter for customer-login
        $customer_id = auth()->user()->customer_id;
        $q->when($customer_id, fn($q) => $q->where('customer_id', $customer_id)); 
        
        $q->when(request('page') == 'pi', fn($q) => $q->where('bank_id', '>', 0));
        $q->when(request('page') == 'qt', fn($q) => $q->where('bank_id', 0));
        
        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
        });

        // client filter
        $q->when(request('client_id'), fn($q) => $q->where('customer_id', request('client_id')));

        $status = true;
        if (request('status_filter')) {
            switch (request('status_filter')) {
                case 'Unapproved':
                    $q->whereNull('approved_by');
                    break;
                case 'Approved & Uninvoiced':
                    $q->whereNotNull('approved_by')->doesntHave('invoice_product');
                    break;
                case 'Approved & Unbudgeted':
                    $q->whereNotNull('approved_by')->whereNull('project_quote_id');
                    break;
                case 'Budgeted & Unverified':
                    $q->whereNotNull('project_quote_id')->whereNull('verified_by');
                    break;
                case 'Verified with LPO & Uninvoiced':
                    $q->whereNotNull('verified_by')->whereNotNull('lpo_id')->where('invoiced', 'No');
                    break;
                case 'Verified without LPO & Uninvoiced':
                    $q->whereNotNull('verified_by')->whereNull('lpo_id')->where('invoiced', 'No');
                    break;
                case 'Approved without LPO & Uninvoiced':
                    $q->whereNotNull('approved_by')->whereNull('lpo_id')->where('invoiced', 'No');
                    break;
                case 'Invoiced & Due':
                    // quotes in due invoices
                    $q->whereHas('invoice_product', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('status', 'due');
                        });
                    });
                    break;
                case 'Invoiced & Partially Paid':
                    // quotes in partially paid invoices
                    $q->whereHas('invoice_product', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('status', 'partial');
                        });
                    });
                    break;
                case 'Invoiced & Paid':
                    // quotes in partially paid invoices
                    $q->whereHas('invoice_product', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('status', 'paid');
                        });
                    });
                    break;
                case 'Cancelled':
                    $status = false;
                    $q->where('status', 'cancelled');
                    break;
            }
        }
        $q->when($status, fn($q) => $q->where('status', '!=', 'cancelled'));
        

        // project quote filter
        $q->when(request('project_id'), function($q) {
            if (request('quote_ids')) $q->whereIn('id', explode(',', request('quote_ids')));
            else $q->whereIn('id', [0]);
        });
        
        return $q->take(10)->orderBy('id','desc')->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'date', 'total', 'status', 'bank_id', 
            'verified', 'revision', 'client_ref', 'lpo_id', 'currency_id', 'approved_date','project_closure_date'
        ]);
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return $quote
     */
    public function create(array $input)
    {
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'reference_date','unapproved_reminder_date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['taxable', 'total', 'subtotal', 'tax']))
                $data[$key] = numberClean($val);
        }   

        // increament tid
        $tid = 0;
        if (@$data['bank_id']) $tid = Quote::where('bank_id', '>', 0)->max('tid');
        else $tid = Quote::where('bank_id', 0)->max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1;

        // set currency
        if (!$data['currency_id'] && $data['customer_id']) {
            $customer = Customer::find($data['customer_id']);
            if ($customer) $data['currency_id'] = @$customer->ar_account->currency_id;
            if (!$data['currency_id']) throw ValidationException::withMessages(["Currency required! Update {$customer->company} A/R account"]);
        }
        
        DB::beginTransaction();
        // close lead
        Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        

        $result = Quote::create($data);
        $stock_transfers = StockTransfer::where('lead_id',$data['lead_id'])->get();
        if (count($stock_transfers) > 0){
            foreach($stock_transfers as $stock_transfer){
                $stock_transfer->quote_id = $result->id;
                $stock_transfer->update();
            }
        }

        // quote line items
        $data_items = $input['data_items'];
        // dd($data_items);
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'quote_id' => $result->id, 
                'ins' => $result->ins,
                'product_price' =>  floatval(str_replace(',', '', $v['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', $v['product_subtotal'])),
                'buy_price' => floatval(str_replace(',', '', $v['buy_price'])),
                'estimate_qty' => floatval(str_replace(',', '', $v['estimate_qty'])),
            ]);
        }, $data_items);
        QuoteItem::insert($data_items);

        // quote labour items
        $skill_items = $input['skill_items'];
        $skill_items = array_map(function ($v) use($result) {
            return array_replace($v, ['quote_id' => $result->id]);
        }, $skill_items);
        BudgetSkillset::insert($skill_items);
        // quote Equipments
        $equipments = $input['equipments'];
        $equipments = array_map(function ($v) use($result) {
            return array_replace($v, [
                'quote_id' => $result->id,
                'ins' => $result->ins,
                'user_id' => auth()->user()->id,
            ]);
        }, $equipments);
        EquipmentQuote::insert($equipments);

        // Update agent-lead status
        $agentLead = @$result->lead->agentLead;
        if ($agentLead) $agentLead->update(['quote_status' => 'quoted']);
        
        if ($result) {
            DB::commit();
            return $result;
        }
        
        throw new GeneralException('Error Creating Quote');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Quote $quote
     * @param  $input
     * @throws GeneralException
     * @return $quote
     */
    public function update($quote, array $input)
    {
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'reference_date','unapproved_reminder_date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['taxable', 'total', 'subtotal', 'tax'])) 
                $data[$key] = numberClean($val);
        }   

        DB::beginTransaction();

        // update lead status
        if ($quote->lead_id != $data['lead_id']) {
            if ($quote->lead) $quote->lead->update(['status' => 0, 'reason' => 'new']);
            Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        }

        // set currency
        if (!$data['currency_id'] && $data['customer_id']) {
            $customer = Customer::find($data['customer_id']);
            if ($customer) $data['currency_id'] = @$customer->ar_account->currency_id;
            if (!$data['currency_id']) throw ValidationException::withMessages(["Currency required! Update {$customer->company} A/R account"]);
        }
        
        $result = $quote->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $quote->products()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['product_price', 'product_subtotal', 'buy_price', 'estimate_qty']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $quote_item = QuoteItem::firstOrNew(['id' => $item['id']]);
            $quote_item->fill(array_replace($item, ['quote_id' => $quote['id'], 'ins' => $quote['ins']]));
            if (!$quote_item->id) unset($quote_item->id);
            $quote_item->save();
        }

        $skill_items = $input['skill_items'];
        // remove omitted items
        $skill_ids = array_map(function ($v) { return $v['skill_id']; }, $skill_items);
        $quote->skill_items()->whereNotIn('id', $skill_ids)->delete();
        // create or update items
        foreach($skill_items as $item) {
            $skillset = BudgetSkillset::firstOrNew(['id' => $item['skill_id']]);         
            $skillset->fill(array_replace($item, ['quote_id' => $quote->id]));
            if (!$skillset->id) unset($skillset->id);
            unset($skillset->skill_id);
            $skillset->save();
        }
        
        $equipments = $input['equipments'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['eqid']; }, $equipments);
        $quote->equipments()->whereNotIn('id', $item_ids)->delete();
        
        // create or update items
        foreach($equipments as $item) {
            $equipment = EquipmentQuote::firstOrNew(['id' => $item['eqid']]);         
            $equipment->fill(array_replace($item, ['quote_id' => $quote->id]));
            if (!$equipment->id) unset($equipment->id);
            unset($equipment->eqid);
            //dd($equipment);
            $equipment->save();
        }

        // Update agent-lead status
        $agentLead = @$result->lead->agentLead;
        if ($agentLead) $agentLead->update(['quote_status' => 'quoted']);
        
        if ($result) {
            DB::commit();
            return $quote;      
        }
               
        throw new GeneralException('Error Updating Quote');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Quote $quote
     * @throws GeneralException
     * @return bool
     */
    public function delete($quote)
    {
        // handle validation
        $type = $quote->bank_id ? 'PI' : 'Quote';
        if ($quote->project_quote || $quote->project()->exists()) throw ValidationException::withMessages([$type .' has attached Project']);
        if ($quote->project()->exists() && $quote->budget()->exists()) throw ValidationException::withMessages([$type .' has attached Budget']);
        if ($quote->verified == 'Yes' && $quote->verified_products()->exists()) throw ValidationException::withMessages([$type .' has been Verified']);
        if ($quote->invoice()->exists()) throw ValidationException::withMessages([$type .' has attached Invoice']);
        if ($quote->invoice_quote()->exists()) throw ValidationException::withMessages([$type .' has attached Detached Invoice']);
        if ($quote->stockIssues()->exists()) throw ValidationException::withMessages([$type .' has attached Issuance Items']);

        DB::beginTransaction();
        // update lead status
        if ($quote->lead) {
            $quote->lead->update(['status' => 0, 'reason' => 'new']);
        }

        $quote->verified_jcs()->delete();
        $quote->verified_products()->delete();
        $quote->products()->delete();
        if ($quote->delete()) {
            DB::commit();
            return true;
        }
    }

    /**
     * Verify Budgeted Project Quote
     */
    public function verify(array $input)
    {
        DB::beginTransaction();

        /** update quote verification */
        $data = $input['data'];
        $quote = Quote::find($data['id']);
        $user_ids = $data['user_id'] ?? [];
        unset($data['user_id']);
        $result = $quote->update([
            'verified' => 'Yes', 
            'verification_date' => date('Y-m-d'),
            'verified_by' => auth()->user()->id,
            'gen_remark' => $data['gen_remark'],
            'project_closure_date' => @$data['project_closure_date'] ? date_for_database($data['project_closure_date']) : null,
            'verified_taxable' => numberClean($data['taxable']), 
            'verified_amount' => numberClean($data['subtotal']),
            'verified_tax' => numberClean($data['tax']), 
            'verified_total' => numberClean($data['total']),
            'expense' => numberClean($data['expense']),
        ]);

        //find Project
        if($quote->project){
            $project = $quote->project;
            $status = Misc::where('section', 2)->where('name','Completed')->first();
            if ($status) $project->update(['status' => $status->id]);
        }

        /** verified products */
        $data_items = $input['data_items'];
        // update or create verified item
        $item_ids = array_map(fn($v) => $v['item_id'], $data_items);
        VerifiedItem::where('quote_id', $data['id'])->whereNotIn('id', $item_ids)->delete();
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'quote_id' => $data['id'],
                'product_qty' => numberClean($item['product_qty']),
                'product_tax' => numberClean($item['product_tax']),
                'tax_rate' => numberClean($item['tax_rate']),
                'product_price' => floatval(str_replace(',', '', $item['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', $item['product_subtotal'])),
                'ins' => auth()->user()->ins
            ]);
            $verify_item = VerifiedItem::firstOrNew(['id' => $item['item_id']]);
            $verify_item->fill($item);
            if (!$verify_item->id) unset($verify_item->id);
            unset($verify_item->item_id);
            $verify_item->save();
        }

        /** job  cards */
        $job_cards = $input['job_cards'];   
        // duplicate jobcard reference
        $references = array_map(fn($v) => $v['reference'], $job_cards);
        $references = VerifiedJc::whereIn('reference', $references)->pluck('reference')->toArray();
        // update or create verified jobcards
        $item_ids = array_map(fn($v) => $v['jcitem_id'], $job_cards);
        VerifiedJc::where('quote_id', $data['id'])->whereNotIn('id', $item_ids)->delete(); 
        foreach ($job_cards as $item) {
            // skip duplicate reference
            if (in_array($item['reference'], $references) && !$item['jcitem_id']) continue;
            $item = array_replace($item, [
                'quote_id' => $data['id'],
                'date' => date_for_database($item['date']),
            ]);
            $jobcard = VerifiedJc::firstOrNew(['id' => $item['jcitem_id']]);
            $jobcard->fill($item);
            if (!$jobcard->id) unset($jobcard->id);
            unset($jobcard->jcitem_id);
            $jobcard->save();
        }

        /**labour allocation */
        $labour_items = $input['labour_items'];
        foreach ($labour_items as $item) {
            if (!$quote->project) continue;
            
            if ($item['job_employee']) {
                $jobcard_no = trim($item['job_jobcard_no']);
                $verified_jc = VerifiedJc::where('quote_id', $quote->id)->where('reference', $jobcard_no)->first();
                $employee_ids = array_filter(explode(',', $item['job_employee']));
                $job_hrs = numberClean($item['job_hrs']);
                // if ($job_hrs > 14) continue;
                
                foreach ($employee_ids as $id) {
                    $labour_data = [
                        'employee_id' => $id,
                        'project_id' => $quote->project->id,
                        'date' => date_for_database($item['job_date']),
                        'ref_type' => $item['job_ref_type'],
                        'job_card' => $jobcard_no,
                        'note' => $item['job_note'],
                        'hrs' => $job_hrs,
                        'type' => $item['job_type'],
                        'is_payable' => $item['job_is_payable'],
                        'verified_jc_id' => @$verified_jc->id,
                        'user_id' => auth()->user()->id,
                        'ins' => auth()->user()->ins,
                    ];
                    // date validation
                    if (strtotime($labour_data['date']) > strtotime(date('Y-m-d'))) continue;
                    // $one_week_earlier = strtotime(date('Y-m-d')) - (7 * 24 * 60 * 60);
                    // if (strtotime($labour_data['date']) < $one_week_earlier) continue; 

                    // save allocation
                    $labour_allocation = LabourAllocation::updateOrCreate([
                        'employee_id' => $id,
                        'job_card' => $labour_data['job_card'],
                    ], $labour_data);
                    // save allocation items
                    unset($labour_data['project_id'], $labour_data['verified_jc_id']);
                    $labour_data['labour_id'] = $labour_allocation->id;
                    LabourAllocationItem::updateOrCreate([
                        'employee_id' => $id,
                        'job_card' => $labour_data['job_card'],
                    ],$labour_data);
                }
            }
        }

        if ($result) {
            DB::commit();
            if(count($user_ids)>0){
                foreach($user_ids as $user_id){
                    $hrm = User::find($user_id);
                    $hrm->notify(new VerifyNotification($quote));
                }
            }
            
            if (
                $quote->project &&
                is_numeric($quote->expense) && is_numeric($quote->verified_amount) &&
                floatval($quote->expense) > 0 &&
                floatval($quote->verified_amount) > 0 &&
                floatval($quote->expense) > floatval($quote->verified_amount)
            ) {
                $this->notify_users_on_loss($quote);
            }
            $this->percentage_below_notify($quote, $data);
            return $quote;      
        }
    }

    public function notify_users_on_loss($quote)
    {
        $project = $quote->project;
        $company = Company::find(auth()->user()->ins);

        $setting = RecipientSetting::where(['type' => 'project_percentage','uom' => '%'])->first();
        if(!$setting) return;
        $quote_tid = $this->generateQuoteTid($quote);
        $project_no = gen4tid("PRJ-", $project->tid);
        $customer_name = $this->getCustomerName($project);
        $branch_name = optional($project->branch()->first())->name ?? 'Head Office';

        $message_template = $this->buildMessageTemplate($project, $project_no, $quote_tid, $customer_name, $branch_name);
        $project_user_ids = $project->users()->get()->pluck('id')->toArray();

        [$user_info, $message_body, $phone_numbers, $user_ids] = $this->collectUserData($project_user_ids, $message_template);

        // Add company to notification list
        $this->addCompanyToNotification($company, $message_template, $user_info, $message_body, $phone_numbers, $user_ids);

        $this->sendBulkSMS($message_template, $phone_numbers, $user_ids, $message_body);
        $this->dispatchNotifications($user_info, "Verification / IPC Valuation");
    }

    private function generateQuoteTid($quote)
    {
        return gen4tid($quote->bank_id ? "PI-" : "QT-", $quote->tid);
    }

    private function getCustomerName($project)
    {
        $customer = $project->customer()->first();
        return $customer->company ?: $customer->name;
    }

    private function buildMessageTemplate($project, $project_no, $quote_tid, $customer_name, $branch_name)
    {
        $date = date('d/m/Y');
        return "Dear :name, Please note your verification / IPC Valuation for Project {$project_no} - {$project->name} under Quotes {$quote_tid}, for {$customer_name} - {$branch_name} is a LOSS as at {$date}!. Please login to your ERP for further review";
    }

    private function collectUserData($user_ids, $template)
    {
        $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';
        $user_info = $message_body = $phone_numbers = $userIds = [];

        foreach ($user_ids as $id) {
            $user = Hrm::find($id);
            $contact = optional($user->meta)->secondary_contact;
            $cleaned = preg_replace('/\D/', '', $contact);

            if ($user && $contact && preg_match($pattern, $cleaned)) {
                $phone = preg_match('/^01\d{8}$/', $cleaned) ? '254' . substr($cleaned, 1) : $cleaned;
                $message = str_replace(":name", $user->fullname, $template);

                $user_info[] = [
                    'user' => [
                        'email' => $user->personal_email,
                        'id' => $user->id,
                        'phone' => $phone,
                    ],
                    'phone' => $phone,
                    'message' => $message,
                ];

                $message_body[] = ['phone' => $phone, 'message' => $message];
                $phone_numbers[] = $phone;
                $userIds[] = $user->id;
            }
        }

        return [$user_info, $message_body, $phone_numbers, $userIds];
    }

    private function addCompanyToNotification($company, $template, &$user_info, &$message_body, &$phones, &$ids)
    {
        $message = str_replace(":name", $company->cname, $template);

        $user_info[] = [
            'user' => [
                'email' => $company->email,
                'id' => $company->id,
                'phone' => $company->notification_number,
            ],
            'phone' => $company->notification_number,
            'message' => $message,
        ];

        $message_body[] = [
            'phone' => $company->notification_number,
            'message' => $message,
        ];

        $phones[] = $company->notification_number;
        $ids[] = $company->id;
    }

    private function sendBulkSMS($template, $phones, $userIds, $message_body)
    {
        if (empty($phones)) return;

        $charCount = strlen($template);
        $cost_per_160 = 0.6;
        $total_cost = $cost_per_160 * ceil($charCount / 160) * count($userIds);

        $sms = SendSms::create([
            'subject' => $template,
            'phone_numbers' => implode(',', $phones),
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'single',
            'sent_to_ids' => implode(',', $userIds),
            'characters' => $charCount,
            'cost' => $cost_per_160,
            'user_count' => count($userIds),
            'total_cost' => $total_cost,
        ]);

        (new RosesmsRepository(auth()->user()->ins))->bulk_personalised_sms($message_body, $sms);
    }

    private function dispatchNotifications($user_info, $subject)
    {
        foreach ($user_info as $info) {
            if (!empty($info['user'])) {
                VerifyNotifyUsers::dispatch(auth()->user()->ins, $info['user'], $info['message'], $subject);
            }
        }
    }



    public function percentage_below_notify($quote, $data)
    {

        foreach ($data as $key => $value) {
            if (is_string($value) && preg_match('/^\d{1,3}(,\d{3})*(\.\d+)?$/', $value)) {
                $data[$key] = (float) str_replace(',', '', $value);
            }
        }
        $project = $quote->project;
        $company = Company::find(auth()->user()->ins);
        $setting = RecipientSetting::where(['type' => 'project_percentage','uom' => '%'])->first();
        if(!$setting) return;
        $subtotal = floatval($data['subtotal'] ?? 0);
        $expense = floatval($data['expense'] ?? 0);

        if ($subtotal <= 0) return; // prevents divide-by-zero and invalid data

        $profit = $subtotal - $expense;
        $percentage_profit = ($profit / $subtotal) * 100;
        if($percentage_profit <= floatval($setting->target)){

            $quote_tid = $this->generateQuoteTid($quote);
            $project_no = gen4tid("PRJ-", $project->tid);
            $customer_name = $this->getCustomerName($project);
            $branch_name = optional($project->branch()->first())->name ?? 'Head Office';
    
            $message_template = $this->percent_message_template($project, $project_no, $quote_tid, $customer_name, $branch_name,$setting);
            $project_user_ids = $project->users()->get()->pluck('id')->toArray();
    
            [$user_info, $message_body, $phone_numbers, $user_ids] = $this->collectUserData($project_user_ids, $message_template);
    
            // Add company to notification list
            $this->addCompanyToNotification($company, $message_template, $user_info, $message_body, $phone_numbers, $user_ids);
    
            $this->sendBulkSMS($message_template, $phone_numbers, $user_ids, $message_body);
            $this->dispatchNotifications($user_info, "Verification / IPC Valuation");
        }
    }

    private function percent_message_template($project, $project_no, $quote_tid, $customer_name, $branch_name, $setting)
    {
        $date = date('d/m/Y');
        return "Dear :name, Please note your verification / IPC Valuation for Project {$project_no} - {$project->name} under Quotes {$quote_tid}, {$quote_tid} for {$customer_name} - {$branch_name} is BELOW {$setting->target}% as at {$date}!. Please login to your ERP for further review";
    }

}
