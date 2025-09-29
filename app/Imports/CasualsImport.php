<?php

namespace App\Imports;

use App\Models\casual\CasualLabourer;
use App\Models\job_category\JobCategory;
use Error;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CasualsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
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
            'Full Name','Id Number','Job Category','Mpesa Number','Alternate Number','Work Type','Gender','Email Address',
            'Where do you stay','Next of Kin Name','Next of Kin Contact','Relationship with Next of Kin'
        ];

        $row_count = 0;
        $label_count = count($columns);
        foreach ($rows as $i => $row) {
            $row = array_slice($row->toArray(), 0, $label_count);
            
            if ($i == 0) {
                $omitted_cols = array_diff($columns, $row);
                if ($omitted_cols) throw new Error('Column label mismatch: ' . implode(', ',$omitted_cols));
                continue;
            }
            $row_data = array_combine($columns, $row);
            $work_type = strtolower(str_replace('-', '_', $row_data['Work Type']));
            $job_cat = strtolower(trim($row_data['Job Category']));
            $job_category = JobCategory::whereRaw('LOWER(name) = ?', [$job_cat])->first();
            $gender = strtolower(trim($row_data['Gender']));
            $home_address = strtolower(trim($row_data['Where do you stay']));
            $kin_name = strtolower(trim($row_data['Next of Kin Name']));
            $kin_contract = strtolower(trim($row_data['Next of Kin Contact']));
            $kin_relationship = strtolower(trim($row_data['Relationship with Next of Kin']));

            $nameArray = explode(" ", $row_data['Full Name']);
            $capitalizedNameArray = array_map('ucfirst', array_map('strtolower', $nameArray));
            $row_data['Full Name'] = implode(" ", $capitalizedNameArray);

            $row_data = array_replace($row_data, [
                'name' => $row_data['Full Name'],
                'id_number' => $row_data['Id Number'],
                'job_category_id' => $job_category ? $job_category->id : null,
                'work_type' => $work_type,
                'phone_number' => $row_data['Mpesa Number'],
                'email' => $row_data['Email Address'],
                'gender' => $gender,
                'home_address' => $home_address,
                'kin_name' => $kin_name,
                'kin_contact' => $kin_contract,
                'kin_relationship' => $kin_relationship,
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ]);
            $keys_to_unset = [
                'Full Name','Id Number','Job Category','Mpesa Number','Alternate Number','Work Type','Gender','Email Address',
                'Where do you stay','Next of Kin Name','Next of Kin Contact','Relationship with Next of Kin'
            ];
            
            // Unsetting the specified keys
            foreach ($keys_to_unset as $key) {
                unset($row_data[$key]);
            }
            // dd($row_data);
            
            $result = CasualLabourer::create($row_data);
            if ($result) $row_count++;
        }

        if (!$row_count) throw new Error('Please fill template with required data');
        $this->row_count = $row_count;
    }

    public function rules(): array
    {
        return [
            'Full Name' => 'required|string',
            'Id Number' => 'required',
            'Job Category' => 'required',
            'Work Type' => 'required',
            'Gender' => 'required',
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
