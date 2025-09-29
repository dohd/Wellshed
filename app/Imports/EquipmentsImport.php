<?php

namespace App\Imports;

use App\Models\equipment\Equipment;
use Error;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EquipmentsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
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
        $columns = [
            'equip_serial','unique_id','capacity','location','machine_gas','make_and_type',
            'model_and_model_no','equipment_category_id','service_rate','building','floor'
        ];
        $tid = Equipment::where('ins', auth()->user()->ins)->max('tid') + 1;

        $row_count = 0;
        $label_count = count($columns);
        foreach ($rows as $i => $row) {
            $row = array_slice($row->toArray(), 0, $label_count);
            
            if ($i == 0) {
                $omitted_cols = array_diff($columns, $row);
                if ($omitted_cols) throw new Error('Please check uploaded template! Template column label mismatch: ' . implode(', ', $omitted_cols));
                continue;
            }

            $row_data = array_combine($columns, $row);
            $row_data = array_replace($row_data, [
                'make_type' => $row_data['make_and_type'],
                'model' => $row_data['model_and_model_no'],
                'tid' => $tid,
                'customer_id' => $this->data['customer_id'],
                'branch_id' => $this->data['branch_id'],
                'ins' => auth()->user()->ins,
            ]);
            unset($row_data['make_and_type'], $row_data['model_and_model_no']);
            foreach ($row_data as $key => $val) {
                if ($key == 'service_rate') 
                    $row_data[$key] = numberClean($row_data['service_rate']);
                if (strcasecmp($val, 'null') == 0) $row_data[$key] = null;
            }

            $result = Equipment::create($row_data);
            if ($result) {
                $row_count++;
                $tid++;
            }
        }

        if (!$row_count) throw new Error('Please fill template with required data');
        $this->row_count = $row_count;
    }

    public function rules(): array
    {
        return [
            '0' => 'required|string',
            '1' => 'required',
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
        return 2;
    }
}
