<?php

namespace App\Imports;

use App\Models\supplier\Supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SuppliersImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private $rows = 0;

    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function collection(Collection $rows)
    {
        // dd($rows, $this->data['currency_id']);
        $supplier_data = [];
        foreach ($rows as $key => $row) {
            $row_num = $key+1;
            if ($row_num == 1 && $row->count() < 9) {
                trigger_error('Missing columns! Use latest CSV file template.');
            } elseif ($row_num > 1) {
                if (empty($row[0])) trigger_error('Company is required on row no. $row_num', );
                $supplier_data[] = [
                    'company' => $row[0],
                    'name' => empty($row[1])? $row[0] : $row[1],
                    'phone' => $row[2],
                    'email' => $row[3],
                    'address' => $row[4],
                    'city' => $row[5],
                    'country' => $row[6],
                    'postbox' => $row[7],
                    'taxid' => $row[8],
                    'ap_account_id' => $this->data['ap_account_id'],
                    'currency_id' => $this->data['currency_id'],
                    'ins' => $this->data['ins'],
                    'user_id' => auth()->user()->id,
                ];
                ++$this->rows;
            }            
        }
        Supplier::insert($supplier_data);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|string',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function startRow(): int
    {
        return 1;
    }
}
