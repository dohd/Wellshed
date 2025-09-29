<?php

namespace App\Jobs;

use App;
use App\Models\items\UtilityBillItem;
use App\Models\utility_bill\UtilityBill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Accounting;
use App\Repositories\Focus\purchase\PurchaseRepository as PurchasePurchaseRepository;
use App\Repositories\purchase\PurchaseRepository;


class UpdateTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Accounting;

    private $purchase;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // foreach ($this->purchases as $purchase) {
            if ($this->purchase->bill) {
                // echo "Purchase ID: {$this->purchase->id} has a bill.\n";
                if ($this->purchase->bill->transactions->isEmpty()) {
                    // echo "Purchase ID: {$this->purchase->id} has a bill but no transactions.\n";
                    $this->purchase->bill_id = $this->purchase->bill->id;
                    $this->post_purchase_expense($this->purchase);
                } 
            } else {
                // echo "Purchase ID: {$this->purchase->id} does not have a bill.\n";
                $bill = $this->generate_bill($this->purchase);
                $this->purchase->bill_id = $bill->id;
                $this->post_purchase_expense($this->purchase);
            }
        // }
    }

    public function generate_bill($purchase)
    {
        dd($purchase);
        $purchase_items = $purchase->items->toArray();
        $bill_items_data = array_map(fn($v) => [
            'ref_id' => $v['id'],
            'note' => "({$v['type']}) {$v['description']} {$v['uom']}",
            'qty' => $v['qty'],
            'subtotal' => $v['qty'] * $v['rate'],
            'tax' => $v['taxrate'],
            'total' => $v['amount'], 
        ], $purchase_items);
        
        $bill_data = [
            'supplier_id' => $purchase->supplier_id,
            'reference' => $purchase->doc_ref,
            'reference_type' => strtolower($purchase->doc_ref_type),
            'document_type' => 'direct_purchase',
            'ref_id' => $purchase->id,
            'date' => $purchase->date,
            'due_date' => $purchase->due_date,
            'tax_rate' => $purchase->tax,
            'subtotal' => $purchase->paidttl,
            'tax' => $purchase->grandtax,
            'total' => $purchase->grandttl,
            'note' => $purchase->note,
        ];
        $bill = UtilityBill::withoutGlobalScopes()->where(['document_type' => 'direct_purchase','ref_id' => $purchase->id])->first();
        
        if ($bill) {
            // update bill
            $bill->update($bill_data);
            foreach ($bill_items_data as $item) {
                $new_item = UtilityBillItem::withoutGlobalScopes()->firstOrNew(['bill_id' => $bill->id,'ref_id' => $item['ref_id']]);
                $new_item->save();
            }
        } else {
            // create bill
            $bill_data['tid'] = UtilityBill::withoutGlobalScopes()->max('tid')+1;
            $bill = UtilityBill::withoutGlobalScopes()->create($bill_data);
            $bill_items_data = array_map(function ($v) use($bill) {
                $v['bill_id'] = $bill->id;
                return $v;
            }, $bill_items_data);
            UtilityBillItem::withoutGlobalScopes()->insert($bill_items_data);
        }
        return $bill;
    }
}
