<?php

namespace App\Imports;

use App\Models\prospect\Prospect;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProspectsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
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
       
        $prospect_data = [];
        foreach ($rows as $key => $row) {
            $row_num = $key + 1;
            if ($row_num == 1 && $row->count() < 6) {
                trigger_error('Missing columns! Use latest CSV file template.');
            } elseif ($row_num > 1) {
                if (empty($row[0])) trigger_error('Company is required on row no. $row_num',);
                $prospect_data[] = [
                    'title' => $this->data['title'],
                    'status'=>'open',
                    'call_status'=>'notcalled',
                    'temperate'=>'warm',
                    'category' => "excel",
                    'company' => $row[0],
                    'contact_person' => $row[1],
                    'industry' => $row[2],
                    'email' => $row[4],
                    'phone' => $row[5],
                    'region' => $row[3],
                    'ins' => $this->data['ins'],
                    'reason' => 'new',
                    'user_id' => auth()->user()->id,
                ];
                ++$this->rows;
            }
        }
        // dd($prospect_data);
       
       Prospect::insert($prospect_data);
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
