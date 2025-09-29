<?php

namespace App\Repositories\Focus\projectstocktransfer;


use App\Models\items\TransferItem;
use App\Models\projectstocktransfer\Projectstocktransfer;
use App\Models\account\Account;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

use App\Models\items\CustomEntry;
use App\Models\items\InvoiceItem;
use App\Models\product\ProductVariation;
use Illuminate\Support\Facades\DB;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Class PurchaseorderRepository.
 */
class ProductstocktransferRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Projectstocktransfer::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

       return $this->query()->where('transaction_type','stock_adjustment')->where('is_bill',3)
        ->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {


  
        $inventory_trans_id = Transactioncategory::where('code', 'stock')->first();
        $inventory_trans_id=$inventory_trans_id->id;

        $account_id = Account::where('system', 'inv')->first();
        $account_id=$account_id->id;


       $grn=Projectstocktransfer::orderBy('grn', 'desc')->where('grn','>',0)->first();
       $grn= @$grn+1;
        $input['invoice']['account_id'] = $account_id;
        $input['invoice']['grn'] = $grn;
        $input['invoice']['project_id'] = $input['invoice']['project_id'];
        $input['invoice']['branch_id'] = $input['invoice']['branch_id'];
        $input['invoice']['payer_id'] = $input['invoice']['payer_id'];
        $input['invoice']['refer_no'] = $input['invoice']['refer_no'];
        $input['invoice']['trans_category_id'] = $inventory_trans_id;
        $input['invoice']['tid'] = $input['invoice']['tid'];
        $input['invoice']['note'] = $input['invoice']['note'];
        $input['invoice']['requested_by'] = $input['invoice']['requested_by'];
        $input['invoice']['approved_by'] = $input['invoice']['approved_by'];
        $input['invoice']['s_warehouses'] = $input['invoice']['s_warehouses'];
        $input['invoice']['is_bill'] = 3;
        $input['invoice']['transaction_type'] ='stock_adjustment';

      
       

        DB::beginTransaction();
         $input['invoice'] = array_map( 'strip_tags', $input['invoice']);
        $result = Projectstocktransfer::create($input['invoice']);
        if ($result) {
                 // dd($result->id);
            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            $stock_update = array();         
//Transfer Items
             foreach ($input['invoice_items']['product_id'] as $key => $value) {

                $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => numberClean($input['invoice_items']['product_qty'][$key]));
                   
    
            $products[] = array(
                    'transaction_id' => $result->id,
                    'ins' => $input['invoice_items']['ins'],
                    'user_id' => $input['invoice_items']['user_id'],
                    'product_id' => $input['invoice_items']['product_id'][$key],
                   // 's_warehouses' => $input['inventory_items']['s_warehouses'],
                    'product_name' => strip_tags(@$input['invoice_items']['product_name'][$key]),
                    'qty' => numberClean(@$input['invoice_items']['product_qty'][$key]),
                    'unit' => strip_tags(@$input['invoice_items']['unit'][$key]),
                     'code' => strip_tags(@$input['invoice_items']['code'][$key]),
                    'product_price' => numberClean(@$input['invoice_items']['product_price'][$key]),
                    'product_subtotal' => numberClean(@$input['invoice_items']['product_subtotal'][$key]),
                    'client_id' => numberClean(@$input['invoice_items']['payer_id'][$key]),
                    'project_id' => numberClean(@$input['invoice_items']['project_id'][$key]),
                    'branch_id' => numberClean(@$input['invoice_items']['branch_id'][$key])
                       
                );
            }

            TransferItem::insert($products);
            $update_variation = new ProductVariation;
            $index = 'id';
            Batch::update($update_variation, $stock_update, $index, true,'-');



//begit debit entry
$purchases_trans_category_id = Transactioncategory::where('code', 'exp')->first();
$purchases_trans_category_id=$purchases_trans_category_id->id;


$account_id = Account::where('system', 'cogs')->first();
$account_id=$account_id->id;

$input['inventory']['bill_id'] =  $result->id;
$input['inventory']['account_id'] = $account_id;
$input['inventory']['project_id'] = $input['inventory']['project_id'];
$input['inventory']['branch_id'] = $input['inventory']['branch_id'];
$input['inventory']['payer_id'] = $input['inventory']['payer_id'];
$input['inventory']['note'] = $input['inventory']['note'];
$input['inventory']['trans_category_id'] =$purchases_trans_category_id;
$input['inventory']['tid'] = $input['inventory']['tid'];
$input['inventory']['transaction_type'] ='cogs';
$input['inventory'] = array_map( 'strip_tags', $input['inventory']);
Projectstocktransfer::create($input['inventory']);

        
//end tax

            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));


    }

    /**
     * For updating the respective Model in storage
     *
     * @param Purchaseorder $purchaseorder
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Purchase $purchase, array $input)
    {
    	



      $purchases_trans_category_id = Transactioncategory::where('code', 'p_material')->first();
       $purchases_trans_category_id=$purchases_trans_category_id->id;

        $id = $input['invoice']['id'];
        $tid = $input['invoice']['tid'];
        $input['invoice']['payer_type'] = $input['invoice']['payer_type'];
        $input['invoice']['payer'] = $input['invoice']['payer'];
        $input['invoice']['payer_id'] = $input['invoice']['payer_id'];
        $input['invoice']['trans_category_id'] = $purchases_trans_category_id;
        $input['invoice']['tid'] = $input['invoice']['tid'];
        $input['invoice']['taxformat'] = $input['invoice']['taxformat'];
        $input['invoice']['discountformat'] = $input['invoice']['discountformat'];
        $input['invoice']['s_warehouses'] = $input['invoice']['s_warehouses'];
        $input['invoice']['is_bill'] = 1;
        $input['invoice']['transaction_type'] ='purchases';
        unset($input['invoice']['id']);
        unset($input['invoice']['tid']);
      
       

        DB::beginTransaction();
         $result = Purchase::find($id);
         $input['invoice'] = array_map( 'strip_tags', $input['invoice']);
       // $result = Purchase::create($input['invoice']);
        $result->update($input['invoice']);
        if ($result) {
                 // dd($result->id);
            Purchase::where('bill_id', $id)->delete();
            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            $stock_update = array();
            $purchases_trans_sec_id = Account::where('system', 'inv')->first();
            $purchases_trans_sec_id=$purchases_trans_sec_id->id;
            
            
             $grn=Purchase::orderBy('grn', 'desc')->where('grn','>',0)->first();
             $grn= @$grn+1;

//purchase product
             if($input['inventory_items']['totalsaleamount']>0){

         $purchases_trans_category_id = Transactioncategory::where('code', 'p_material')->first();
            $purchases_trans_category_id=$purchases_trans_category_id->id;
            foreach ($input['inventory_items']['product_id'] as $key => $value) {
    
  
                  //if project take to work in progress account
                 if(!empty( $input['inventory_items']['inventory_project_id'][$key]) || ($input['inventory_items']['inventory_project_id'][$key])>0){
                     $account_id = Account::where('system', 'cogs')->first();
                      $account_id=$account_id->id;
                      $grn="0";
                    
                     

                 }else{
                     $account_id = Account::where('system', 'inv')->first();
                      $account_id=$account_id->id;

                      $stock_update[] = array('id' => $input['inventory_items']['product_id'][$key], 'qty' => numberClean($input['inventory_items']['product_qty'][$key]));
                      $grn= $grn;


                 }

                
            $products[] = array(
                    'bill_id' => $result->id,
                    'tid' => $input['inventory_items']['tid'],
                    'ins' => $input['inventory_items']['ins'],
                    'user_id' => $input['inventory_items']['user_id'],
                    'account_id' => $account_id,
                    'secondary_account_id' => $purchases_trans_sec_id,
                    'trans_category_id' => $purchases_trans_category_id,
                    'grn' => $grn,
                    'transaction_tab' => 1,
                    'transaction_type' =>'inventory',
                    'transaction_date' => $input['inventory_items']['transaction_date'],
                    's_warehouses' => $input['inventory_items']['s_warehouses'],
                    'item_id' => strip_tags(@$input['inventory_items']['product_id'][$key]),
                    'item_name' => strip_tags(@$input['inventory_items']['product_name'][$key]),
                    'qty' => strip_tags(@$input['inventory_items']['product_qty'][$key]),
                    'unit' => strip_tags(@$input['inventory_items']['u_m'][$key]),
                    'debit' => numberClean(@$input['inventory_items']['salevalue'][$key]),
                    'total_amount' => numberClean(@$input['inventory_items']['product_subtotal'][$key]),
                    'rate' => numberClean(@$input['inventory_items']['product_price'][$key]),
                    'taxable_amount' => numberClean(@$input['inventory_items']['taxedvalue'][$key]),
                    'tax_amount' => numberClean(@$input['inventory_items']['total_tax'][$key]),
                    'tax' => numberClean(@$input['inventory_items']['product_tax'][$key]),
                    'discount_rate' => numberClean(@$input['inventory_items']['product_discount'][$key]),
                    'discount' => numberClean(@$input['inventory_items']['total_discount'][$key]),
                    'note' => strip_tags(@$input['inventory_items']['product_description'][$key]),
                    //'payer_id' => numberClean(@$input['inventory_items']['client_id'][$key]),
                    'project_id' => numberClean(@$input['inventory_items']['inventory_project_id'][$key]),
                    'branch_id' => numberClean(@$input['inventory_items']['branch_id'][$key])
                       
                );
            }


            Purchase::insert($products);
            $update_variation = new ProductVariation;
            $index = 'id';
            Batch::update($update_variation, $stock_update, $index, true,'+');

        }

//end inventory items





//start expense tab
             if($input['expense_items']['exp_totalsaleamount']>0){

            $purchases_trans_category_id = Transactioncategory::where('code', 'exp')->first();
            $purchases_trans_category_id=$purchases_trans_category_id->id;
            foreach ($input['expense_items']['ledger_id'] as $key => $value) {


    
  
                  //if project post to work in progress ledger
                 if(!empty( $input['expense_items']['exp_project_id'][$key]) || ($input['expense_items']['exp_project_id'][$key])>0){
                     $account_id = Account::where('system', 'cogs')->first();
                      $account_id=$account_id->id;
            
                    
                     

                 }else{
                     $account_id = $input['expense_items']['ledger_id'][$key];
                   
                 }

                
               $expenses[] = array(
                    'bill_id' => $result->id,
                    'tid' => $input['expense_items']['tid'],
                    'ins' => $input['expense_items']['ins'],
                    'user_id' => $input['expense_items']['user_id'],
                    'account_id' => $account_id,
                    'secondary_account_id' => $input['expense_items']['ledger_id'][$key],
                    'trans_category_id' => $purchases_trans_category_id,
                    'transaction_tab' => 2,
                    'transaction_type' =>'expenses',
                    'transaction_date' => $input['expense_items']['transaction_date'],
                    'qty' => strip_tags(@$input['expense_items']['exp_product_qty'][$key]),
                    'debit' => numberClean(@$input['expense_items']['exp_salevalue'][$key]),
                    'total_amount' => numberClean(@$input['expense_items']['exp_product_subtotal'][$key]),
                    'rate' => numberClean(@$input['expense_items']['exp_product_price'][$key]),
                    'taxable_amount' => numberClean(@$input['expense_items']['exp_taxedvalue'][$key]),
                    'tax_amount' => numberClean(@$input['expense_items']['exp_total_tax'][$key]),
                    'tax' => numberClean(@$input['expense_items']['exp_product_tax'][$key]),
                    'discount_rate' => numberClean(@$input['expense_items']['exp_product_discount'][$key]),
                    'discount' => numberClean(@$input['expense_items']['exp_total_discount'][$key]),
                    'note' => strip_tags(@$input['expense_items']['exp_product_description'][$key]),
                   // 'payer_id' => numberClean(@$input['expense_items']['exp_client_id'][$key]),
                    'project_id' => numberClean(@$input['expense_items']['exp_project_id'][$key]),
                    'branch_id' => numberClean(@$input['expense_items']['exp_branch_id'][$key])
                       
                );
            }


            Purchase::insert($expenses);
           

        }

//end expense tab



//begit tax
if($input['tax']['tax_amount']>0){
     $purchases_trans_category_id = Transactioncategory::where('code', 'p_taxes')->first();
            $purchases_trans_category_id=$purchases_trans_category_id->id;
    $account_id = Account::where('system', 'tax')->first();
 $account_id=$account_id->id;
 $input['tax']['bill_id'] =  $result->id;
 $input['tax']['account_id'] = $account_id;
 $input['tax']['trans_category_id'] =$purchases_trans_category_id;
 $input['tax']['secondary_account_id'] =$account_id;
 $input['tax']['tax_type'] ='sales_purchases';
 $input['tax']['transaction_type'] ='vat';


$input['tax'] = array_map( 'strip_tags', $input['tax']);
   Purchase::create($input['tax']);
}
        
//end tax

            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.purchaseorders.update_error'));


/*


        $extra_discount = numberClean($input['invoice']['after_disc']);
        $input['invoice']['invoicedate'] = date_for_database($input['invoice']['invoicedate']);
        $input['invoice']['invoiceduedate'] = date_for_database($input['invoice']['invoiceduedate']);
        $input['invoice']['subtotal'] = numberClean($input['invoice']['subtotal']);
        $input['invoice']['shipping'] = numberClean($input['invoice']['shipping']);
        $input['invoice']['discount_rate'] = numberClean($input['invoice']['discount_rate']);
        $input['invoice']['after_disc'] = numberClean($input['invoice']['after_disc']);
        $input['invoice']['total'] = numberClean($input['invoice']['total']);
        $input['invoice']['ship_tax_rate'] = numberClean($input['invoice']['ship_rate']);
        $input['invoice']['ship_tax'] = numberClean($input['invoice']['ship_tax']);
        $input['invoice']['extra_discount'] = $extra_discount;
        $total_discount = $extra_discount;
        $re_stock = @$input['invoice']['restock'];
        unset($input['invoice']['after_disc']);
        unset($input['invoice']['ship_rate']);
        unset($input['invoice']['id']);
        unset($input['invoice']['restock']);
        $result = Purchaseorder::find($id);
        if ($result->status == 'canceled') return false;
         $input['invoice'] = array_map( 'strip_tags', $input['invoice']);
        $result->update($input['invoice']);
        if ($result) {
            PurchaseItem::where('bill_id', $id)->delete();
            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            foreach ($input['invoice_items']['product_id'] as $key => $value) {
                $subtotal += numberClean(@$input['invoice_items']['product_price'][$key]) * numberClean(@$input['invoice_items']['product_qty'][$key]);
                $qty=numberClean($input['invoice_items']['product_qty'][$key]);
                $old_qty=numberClean(@$input['invoice_items']['old_product_qty'][$key]);
                $total_qty += $qty;
                $total_tax += numberClean(@$input['invoice_items']['product_tax'][$key]);
                $total_discount += numberClean(@$input['invoice_items']['total_discount'][$key]);
                $products[] = array('bill_id' => $id,
                    'product_id' => $input['invoice_items']['product_id'][$key],
                    'product_name' => strip_tags(@$input['invoice_items']['product_name'][$key]),
                    'code' => @$input['invoice_items']['code'][$key],
                    'product_qty' => numberClean(@$input['invoice_items']['product_qty'][$key]),
                    'product_price' => numberClean(@$input['invoice_items']['product_price'][$key]),
                    'product_tax' => numberClean(@$input['invoice_items']['product_tax'][$key]),
                    'product_discount' => numberClean(@$input['invoice_items']['product_discount'][$key]),
                    'product_subtotal' => numberClean(@$input['invoice_items']['product_subtotal'][$key]),
                    'total_tax' => numberClean(@$input['invoice_items']['total_tax'][$key]),
                    'total_discount' => numberClean(@$input['invoice_items']['total_discount'][$key]),
                    'product_des' => strip_tags(@$input['invoice_items']['product_description'][$key],config('general.allowed')),
                    'i_class' => 0,
                    'unit' => $input['invoice_items']['unit'][$key], 'ins' => $input['invoice']['ins']);

                if($old_qty>0){
                     $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => $qty-$old_qty);
                }
                else {
                      $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => $qty);
                }
            }
            PurchaseItem::insert($products);
            $invoice_d = Purchaseorder::find($id);
            $invoice_d->subtotal = $subtotal;
            $invoice_d->tax = $total_tax;
            $invoice_d->discount = $total_discount;
            $invoice_d->items = $total_qty;
            $invoice_d->save();
            if (isset($input['data2']['custom_field'])) {
                foreach ($input['data2']['custom_field'] as $key => $value) {
                    $fields[] = array('custom_field_id' => $key, 'rid' => $id, 'module' => 9, 'data' => strip_tags($value), 'ins' => $input['invoice']['ins']);
                    CustomEntry::where('custom_field_id', '=', $key)->where('rid', '=', $id)->delete();
                }
                CustomEntry::insert($fields);
            }
            $update_variation = new ProductVariation;
            $index = 'id';
            Batch::update($update_variation, $stock_update, $index, true);
            if (is_array($re_stock)) {
                $stock_update_one=array();
                foreach ($re_stock as $key => $value) {
                    $myArray = explode('-', $value);
                    $s_id = $myArray[0];
                    $s_qty = numberClean($myArray[1]);
                    if ($s_id) $stock_update_one[] = array('id' => $s_id, 'qty' => $s_qty);
                }
            Batch::update($update_variation, $stock_update_one, $index, true, '+');
            }
            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.purchaseorders.update_error'));*/
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Purchaseorder $purchaseorder
     * @throws GeneralException
     * @return bool
     */
    public function delete(Purchaseorder $purchaseorder)
    {
        if ($purchaseorder->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.purchaseorders.delete_error'));
    }
}
