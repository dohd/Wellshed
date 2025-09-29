<?php

namespace App\Imports;

use App\Models\boq\BoQ;
use App\Models\boq\BoQItem;
use App\Models\boq\BoQWorkSheet;
use DB;
use Error;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BoQsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
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
        // if (empty($this->data['name'])) {
        //     throw ValidationException::withMessages(['Title is required!']);
        // }

        // dd($this->data['item_type']);
        $item_type = $this->data['item_type'];
        if($item_type == 'existing'){
            $boq = BoQ::find($this->data['boq_id']);
        }else if($item_type == 'new'){
            $name = $this->data['name'];
            $data = [
                'name' => $name,
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ];

            $boq = BoQ::create($data);
        }
        $sheet_item = [
            'boq_id' => $boq->id,
            'boq_sheet_id' => $this->data['boq_sheet_id'],
        ];
        $boq_worksheet = BoQWorkSheet::where(
            [
                'boq_id' => $boq->id,
                'boq_sheet_id' => $this->data['boq_sheet_id'],
            ]
        )->first();
        if(!$boq_worksheet)
        {
            $boq_worksheet = BoQWorkSheet::create($sheet_item);
        }
        // dd($boq);
        

        // Normalize column names to uppercase for case-insensitive comparison
        $columns = [
            'ITEM', 'DESCRIPTION', 'UOM', 'QTY', 'RATE', 'AMOUNT'
        ];
        
        // Normalize the input to handle case-insensitive column names
        $row_count = 0;
        //Check if existing column
        if($item_type == 'existing'){
            $row_count = $boq->items()->count();
        }
        $label_count = count($columns);
        
        foreach ($rows as $i => $row) {
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
            
            if (strtolower(trim($normalized_row['QTY'])) === 'qty') {
                continue;
            }
            
            if (strtolower(trim($normalized_row['QTY'])) === 'item' || strtolower(trim($normalized_row['QTY'])) === 'lot') {
                $normalized_row['QTY'] = 1;
                $normalized_row['RATE'] = isset($normalized_row['RATE']) && $normalized_row['RATE'] > 0 
                    ? $normalized_row['RATE'] 
                    : ($normalized_row['AMOUNT'] ?? 0); // Ensure AMOUNT exists
            
            }
            if(trim($normalized_row['QTY'])  > 0 && $normalized_row['RATE'] == '')
            {
                $normalized_row['RATE'] = $normalized_row['AMOUNT'];
            }

            if(empty($normalized_row['UOM']) && !empty($normalized_row['QTY']) && array_filter($normalized_row, function ($value) {
                return !is_null($value) && trim($value) !== '';
            }))
            {
                throw new Error('UoM is missing on item: ' . $normalized_row['DESCRIPTION']);
            }
            $uom = trim($normalized_row['UOM'] ?? '');
            $qty = trim($normalized_row['QTY'] ?? '');

            if (!empty($uom) && $qty === '') {
                throw new Error('Item Missing QTY or UoM should be removed on ' . ($normalized_row['DESCRIPTION'] ?? 'Unknown'));
            }
            

            // Handle data processing
            $product_type = '';
            if (empty($normalized_row['UOM']) && empty($normalized_row['QTY']) && !empty($normalized_row['DESCRIPTION'])) {
                $product_type = 'title';
            } elseif (!empty($normalized_row['UOM']) && !empty($normalized_row['QTY']) && !empty($normalized_row['DESCRIPTION'])) {
                $product_type = 'product';
            } elseif (empty($normalized_row['UOM']) && empty($normalized_row['QTY']) && empty($normalized_row['DESCRIPTION'])) {
                continue;
            } 
            $qty = is_numeric($normalized_row['QTY']) ? (float) $normalized_row['QTY'] : 0;
            $rate = is_numeric($normalized_row['RATE']) ? (float) $normalized_row['RATE'] : 0;

            $amount = $rate * $qty;
           

            $normalized_row = array_replace($normalized_row, [
                'boq_id' => $boq->id,
                'boq_sheet_id' => $this->data['boq_sheet_id'],
                'product_id' => 0,
                'numbering' => $normalized_row['ITEM'],
                'description' => $normalized_row['DESCRIPTION'],
                'new_qty' => $normalized_row['QTY'],
                'boq_rate' => $normalized_row['RATE'],
                'type' => $product_type,
               'boq_amount' => ($amount > 0) ? $amount : ($normalized_row['AMOUNT'] ?? 0),
                'uom' => $normalized_row['UOM'],
                'row_index' => $row_count,
                'misc' => 0,
                'is_imported' => 1,
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ]);
            // dd($normalized_row);

            $keys_to_unset = ['ITEM', 'DESCRIPTION', 'UOM', 'QTY', 'RATE', 'AMOUNT'];

            // Unset specified keys
            foreach ($keys_to_unset as $key) {
                unset($normalized_row[$key]);
            }

            // Clean and process the values
            foreach ($normalized_row as $key => $val) {
                if ($key == 'boq_rate') $normalized_row[$key] = numberClean($normalized_row['boq_rate']);
                if ($key == 'new_qty') $normalized_row[$key] = numberClean($normalized_row['new_qty']);
                if ($key == 'boq_amount') $normalized_row[$key] = numberClean($normalized_row['boq_amount']);
                if (strcasecmp($val, 'null') == 0) $normalized_row[$key] = null;
            }

            // Insert row data into the database
            try {
                DB::beginTransaction();
                $result = BoQItem::create($normalized_row);

                if ($result) {
                    DB::commit();
                }
            } catch (\Throwable $th) {dd($th);
                DB::rollback();
                return errorHandler($th);
            }

            if ($result) {
                $row_count++;
            }
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
