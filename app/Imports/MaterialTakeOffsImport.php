<?php

namespace App\Imports;

use App\Models\boq\BoQ;
use App\Models\boq\BoQItem;
use App\Models\boq\BoQWorkSheet;
use App\Models\items\QuoteItem;
use App\Models\product\ProductVariation;
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\quote\Quote;
use DB;
use Error;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MaterialTakeOffsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
{
    /**
     *
     * @var int $row_count
     */
    private $row_count = 0;

    /**
     *
     * @var array $data
     */
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * 
     * @param Illuminate\Support\Collection $rows
     * @return void
     */
    public function collection(Collection $rows)
    {
        $item_type = $this->data['item_type'];
        $quote = '';
        $budget = '';
        if($item_type == 'quote'){
            $quote = Quote::find($this->data['quote_id']);
        }else if($item_type == 'budget'){
            $budget = Budget::find($this->data['budget_id']);
        }

        

        // Normalize column names to uppercase for case-insensitive comparison
        $columns = [
            'ITEM', 'DESCRIPTION', 'QTY','PRODUCT CODE', 'RATE', 'TYPE'
        ];
        
        // Normalize the input to handle case-insensitive column names
        $row_count = 0;
        //Check if existing column
        if($item_type == 'quote'){
            $row_count = $quote->products()->count();
        }
        else if($item_type == 'budget')
        {
            $row_count = $budget->items()->count();
        }
        $label_count = count($columns);
        
        foreach ($rows as $i => $row) {
            $row_count++;
            $row = array_slice($row->toArray(), 0, $label_count);

            // Convert the row headers to uppercase for comparison
            if ($i == 0) {
                $normalized_row = array_map('strtoupper', $row);
                $omitted_cols = array_diff($columns, $normalized_row);
                if ($omitted_cols) {
                    throw new Error('Column label mismatch: ' . implode(', ', $omitted_cols));
                }
                continue;
            }

            // Create an associative array with the original column names
            $normalized_row = array_combine($columns, $row);
            
            $product = ProductVariation::where('code',trim($normalized_row['PRODUCT CODE']))->first();
            $purchase_price = 0; // Define it outside so it's accessible later
            $price = 0; // Also define price if needed outside
            $tax = 0;
            $product_tax = 0;
            $selling_price = 0;
            if($product){

                $purchase_price = fifoCost($product->id) > 0 ? fifoCost($product->id) : numberClean($product->purchase_price);
                $price = numberClean($normalized_row['RATE']) > 0 ? numberClean($normalized_row['RATE']) :  $product->price;
                $tax = 0;
                if($quote){

                    $tax = $quote->tax_id != 0 ? $quote->tax_id / 100 : 0;
                }
                $product_tax = $purchase_price;
                $selling_price = (1+ $tax) * $price;
            }else{
                $price = numberClean($normalized_row['RATE']) > 0 ? numberClean($normalized_row['RATE']) : 0;
            }
            if($item_type == 'quote' && $quote)
            {
                if(trim($normalized_row['TYPE']) == 'mto'){
                    $normalized_row = array_replace($normalized_row, [
                        'numbering' => $normalized_row['ITEM'],
                        'product_id' => $product->id ?? 0,
                        'product_name' => $product->name ?? $normalized_row['DESCRIPTION'],
                        'product_qty' => 0,
                        'product_subtotal' => $purchase_price,
                        'product_price' => $product_tax,
                        'unit' => @$product->product->unit->code ?? 'TBD',
                        'tax_rate' => $quote->tax_id,
                        'estimate_qty' => $normalized_row['QTY'],
                        'a_type' => 1,
                        'buy_price' => $purchase_price > 0 ? $purchase_price : $price,
                        'row_index' => $row_count,
                        'product_type' => 'inventory_product',
                        'client_product_id' => 0,
                        'misc' => 1,
                        'quote_id' => $quote->id,
                        'ins' => auth()->user()->ins,
                    ]);
                }
                else if(trim($normalized_row['TYPE']) == 'product')
                {
                    $normalized_row = array_replace($normalized_row, [
                        'numbering' => $normalized_row['ITEM'],
                        'product_id' => $product->id ?? 0,
                        'product_name' => $product->name ?? $normalized_row['DESCRIPTION'],
                        'product_qty' => $normalized_row['QTY'],
                        'product_subtotal' => $price,
                        'product_price' => $selling_price,
                        'unit' => @$product->product->unit->code ?? 'TBD',
                        'tax_rate' => $quote->tax_id,
                        'estimate_qty' => $normalized_row['QTY'],
                        'a_type' => 1,
                        'buy_price' => $purchase_price,
                        'row_index' => $row_count,
                        'product_type' => 'inventory_product',
                        'client_product_id' => 0,
                        'misc' => 0,
                        'quote_id' => $quote->id,
                        'ins' => auth()->user()->ins,
                    ]);
                }
                else if(trim($normalized_row['TYPE']) == 'title')
                {
                    $normalized_row = array_replace($normalized_row, [
                        'numbering' => $normalized_row['ITEM'],
                        'product_id' => 0,
                        'product_name' => $normalized_row['DESCRIPTION'],
                        'product_qty' => 0,
                        'product_subtotal' => 0,
                        'product_price' => 0,
                        'unit' => '',
                        'tax_rate' => 0,
                        'estimate_qty' => 0,
                        'a_type' => 2,
                        'buy_price' => 0,
                        'row_index' => $row_count,
                        'product_type' => 'title',
                        'client_product_id' => 0,
                        'misc' => 0,
                        'quote_id' => $quote->id,
                        'ins' => auth()->user()->ins,
                    ]);
                }
                
                $keys_to_unset = ['ITEM', 'DESCRIPTION', 'QTY','PRODUCT CODE', 'RATE', 'TYPE'];
    
                // Unset specified keys
                foreach ($keys_to_unset as $key) {
                    unset($normalized_row[$key]);
                }
    
                // Clean and process the values
                foreach ($normalized_row as $key => $val) {
                    if ($key == 'product_price') $normalized_row[$key] = numberClean($normalized_row['product_price']);
                    if ($key == 'estimate_qty') $normalized_row[$key] = numberClean($normalized_row['estimate_qty']);
                    if ($key == 'product_subtotal') $normalized_row[$key] = numberClean($normalized_row['product_subtotal']);
                    if ($key == 'buy_price') $normalized_row[$key] = numberClean($normalized_row['buy_price']);
                    if (strcasecmp($val, 'null') == 0) $normalized_row[$key] = null;
                }
            }else if($budget){
                if(trim($normalized_row['TYPE']) == 'mto'){
                    $normalized_row = array_replace($normalized_row, [
                        'numbering' => $normalized_row['ITEM'],
                        'product_id' => $product->id ?? 0,
                        'product_name' => $product->name ?? $normalized_row['DESCRIPTION'],
                        'product_qty' => 0,
                        'unit' => @$product->product->unit->code ?? 'TBD',
                        'new_qty' => $normalized_row['QTY'],
                        'a_type' => 1,
                        'price' => $purchase_price,
                        'row_index' => $row_count,
                        'misc' => 1,
                        'budget_id' => $budget->id,
                    ]);
                }
                else if(trim($normalized_row['TYPE']) == 'product'){
                    $normalized_row = array_replace($normalized_row, [
                        'numbering' => $normalized_row['ITEM'],
                        'product_id' => $product->id ?? 0,
                        'product_name' => $product->name ?? $normalized_row['DESCRIPTION'],
                        'product_qty' => 0,
                        'unit' => @$product->product->unit->code ?? 'TBD',
                        'new_qty' => $normalized_row['QTY'],
                        'a_type' => 1,
                        'price' => $purchase_price,
                        'row_index' => $row_count,
                        'misc' => 0,
                        'budget_id' => $budget->id,
                    ]);
                }
                else if(trim($normalized_row['TYPE']) == 'title'){
                    $normalized_row = array_replace($normalized_row, [
                        'numbering' => $normalized_row['ITEM'],
                        'product_id' => 0,
                        'product_name' => $normalized_row['DESCRIPTION'],
                        'product_qty' => 0,
                        'unit' => '',
                        'new_qty' => 0,
                        'a_type' => 2,
                        'price' => 0,
                        'row_index' => $row_count,
                        'misc' => 0,
                        'budget_id' => $budget->id,
                    ]);
                }

                $keys_to_unset = ['ITEM', 'DESCRIPTION', 'QTY', 'PRODUCT CODE', 'RATE', 'TYPE'];
    
                // Unset specified keys
                foreach ($keys_to_unset as $key) {
                    unset($normalized_row[$key]);
                }
    
                // Clean and process the values
                foreach ($normalized_row as $key => $val) {
                    if ($key == 'price') $normalized_row[$key] = numberClean($normalized_row['price']);
                    if ($key == 'new_qty') $normalized_row[$key] = numberClean($normalized_row['new_qty']);
                    if (strcasecmp($val, 'null') == 0) $normalized_row[$key] = null;
                }
            }

            // Insert row data into the database
            try {
                DB::beginTransaction();
                if($quote){
                    $result = QuoteItem::create($normalized_row);
                }elseif($budget)
                {
                    $result = BudgetItem::create($normalized_row);
                }

                if ($result) {
                    DB::commit();
                }
            } catch (\Throwable $th) {dd($th);
                DB::rollback();
                return errorHandler($th);
            }

        }

        if($quote){
            $taxable = 0;
            $total = 0;
            $subtotal = 0;

            foreach ($quote->products as $item) {
                $isMisc = isset($item['misc']) && $item['misc'] > 0;
                $qty = (float)$item['product_qty'];

                if ($qty > 0) {
                    if ($isMisc) {
                        $buyprice = (float)$item['buy_price'];
                        $estqty = (float)$item['estimate_qty'];
                        $taxrate = (float)$item['tax_rate'];

                        
                    } else {
                        $taxrate = (float)$item['tax_rate'];
                        $rate = (float)$item['product_subtotal'];
                        $taxRate = (float)$item['tax_rate'];
                        $price = $rate * ($taxrate / 100 + 1);
                        

                        if ($taxRate > 0) {
                            $taxable += $qty * $rate;
                        }
                        $amount = $price*$qty;

                        $total += $amount;
                        $subtotal += $qty * $rate;
                    }
                }
            }

            $tax = $total - $subtotal;
            $quote->tax = $tax;
            $quote->taxable = $taxable;
            $quote->subtotal = $subtotal;
            $quote->total = $total;
            $quote->update();

        }else if($budget)
        {
            $total = 0;
            foreach($budget->items as $item)
            {
                $qty = (float)$item['new_qty'];
                $price = (float)$item['price'];
                $amount = $qty*$price;
                $total += $amount;
            }
            $budget->budget_total = $total;
            $budget->update();
        }

        if (!$row_count) {
            throw new Error('Please fill template with required data');
        }

        $this->row_count = $row_count;
    }


    public function rules(): array
    {
        return [
            // '0' => 'required|string',
            // '1' => 'required',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function getRowCount(): int
    {
        return $this->row_count;
    }

    public function startRow(): int
    {
        return 1;
    }
}
