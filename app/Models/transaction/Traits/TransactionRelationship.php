<?php

namespace App\Models\transaction\Traits;

use App\Models\banktransfer\Banktransfer;
use App\Models\billpayment\Billpayment;
use App\Models\charge\Charge;
use App\Models\creditnote\CreditNote;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\hrm\Hrm;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\PurchaseItem;
use App\Models\loan\Loan;
use App\Models\loan\Paidloan;
use App\Models\manualjournal\Journal;
use App\Models\opening_stock\OpeningStock;
use App\Models\product\ProductVariation;
use App\Models\project\Project;
use App\Models\sale_return\SaleReturn;
use App\Models\stock_adj\StockAdj;
use App\Models\stock_issue\StockIssue;
use App\Models\utility_bill\UtilityBill;
use App\Models\withholding\Withholding;

/**
 * Class TransactionRelationship
 */
trait TransactionRelationship
{
    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    } 

    public function project()
    {
        return $this->belongsTo(Project::class);
    } 

    public function grn_item() 
    {
        return $this->belongsTo(GoodsreceivenoteItem::class, 'grn_item_id');
    }

    public function sale_return() 
    {
        return $this->belongsTo(SaleReturn::class, 'sale_return_id');
    }

    public function stock_issue() 
    {
        return $this->belongsTo(StockIssue::class, 'stock_issue_id');
    }

    public function stock_adj() 
    {
        return $this->belongsTo(StockAdj::class, 'stock_adj_id');
    }

    public function opening_stock() 
    {
        return $this->belongsTo(OpeningStock::class, 'opening_stock_id');
    }

    public function transfer() 
    {
        return $this->belongsTo(Banktransfer::class, 'bank_transfer_id');
    }

    public function purchase_item() 
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }

    public function grn() 
    {
        return $this->belongsTo(Goodsreceivenote::class, 'grn_id');
    }

    public function invoice_payment()
    {
        return $this->belongsTo(InvoicePayment::class, 'deposit_id');
    }

    public function bill_payment()
    {
        return $this->belongsTo(Billpayment::class, 'payment_id');
    }

    public function manualjournal() 
    {
        return $this->belongsTo(Journal::class, 'man_journal_id');
    }

    public function customer_manualjournal() 
    {
        return $this->belongsTo(Journal::class, 'man_journal_id')->where('customer_id', '>', 0);
    }

    public function supplier_manualjournal() 
    {
        return $this->belongsTo(Journal::class, 'man_journal_id')->where('supplier_id', '>', 0);
    }

    public function debitnote()
    {
        return $this->belongsTo(CreditNote::class, 'dnote_id');
    }

    public function creditnote()
    {
        return $this->belongsTo(CreditNote::class, 'cnote_id');
    }

    public function withholding()
    {
        return $this->belongsTo(Withholding::class, 'wht_id');
    }

    public function charge()
    {
        return $this->belongsTo(Charge::class, 'charge_id');
    }

    public function paidloan()
    {
        return $this->belongsTo(Paidloan::class, 'tr_ref');
    }
    
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'tr_ref');
    }

    public function paidinvoice()
    {
        return $this->belongsTo(InvoicePayment::class, 'tr_ref');
    }
    
    public function invoice()
    {
        return $this->belongsTo('App\Models\invoice\Invoice', 'invoice_id');
    }

    public function deposit()
    {
        return $this->belongsTo(InvoicePayment::class, 'deposit_id');
    }

    public function paidbill()
    {
        return $this->belongsTo(Billpayment::class, 'tr_ref');
    }

    public function bill()
    {
        return $this->belongsTo(UtilityBill::class, 'bill_id');
    }

    public function direct_purchase_bill()
    {
        return $this->belongsTo(UtilityBill::class, 'tr_ref', 'ref_id')->where('document_type', 'direct_purchase');
    }

    public function grn_invoice_bill()
    {
        return $this->belongsTo(UtilityBill::class, 'tr_ref')->where('document_type', 'goods_receive_note')->whereNotNull('ref_id');
    }

    public function grn_bill()
    {
        return $this->belongsTo(UtilityBill::class, 'tr_ref')->where('document_type', 'goods_receive_note')->whereNull('ref_id');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\account\Account');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer', 'customer_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\supplier\Supplier', 'supplier_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Hrm::class, 'payer_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\transactioncategory\Transactioncategory', 'trans_category_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Access\User\User')->withoutGlobalScopes();
    }
}
