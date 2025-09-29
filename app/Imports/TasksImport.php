<?php

namespace App\Imports;

use App\Models\project\Task;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\ValidationException;

class TasksImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private $rows;

    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->rows = 0;
    }

    public function collection(Collection $rows)
    {
        // dd($rows);
        if (empty($this->data['project_id'])) {
            throw ValidationException::withMessages(['Project is required!']);
        }
        $projectId = $this->data['project_id'];

        foreach ($rows as $key => $row) {
            $row_num = $key+1;

            // headers
            // if ($row_num == 1 && $row->count() < 12) {
            //     throw ValidationException::withMessages(['Missing columns! Use latest CSV file template.']);
            // } 

            // tasks
            if ($row_num > 1) {
                // validations
                $errorMsg = '';
                if (empty($row[0])) $errorMsg = "Item type is required on row $row_num";
                if (empty($row[1])) $errorMsg = "Task name is required on row $row_num";
                if (empty($row[2])) $errorMsg = "Duration is required on row $row_num";
                if (empty($row[3])) $errorMsg = "Start date is required on row $row_num";
                if (!is_numeric($row[2])) $errorMsg = "Duration should be a number on row $row_num";
                if (!date_for_database($row[3])) $errorMsg = "YYYY-MM-DD is the expected date format on row $row_num";
                if ($errorMsg) throw ValidationException::withMessages([$errorMsg]);

                $row[1] = preg_replace('/\s+/', ' ', trim($row[1])); // task name

                // skip duplicate product
                $exists = Task::where('project_id', $projectId)->where('name', 'LIKE', '%'. $row[1] .'%')->exists();
                if ($exists) continue;

                $duration = (int) numberClean($row[2]);
                $date = date_for_database($row[3]);
                $duedate = date('Y-m-d', strtotime($date . " +{$duration} days"));
                // validate duration
                $diffInSeconds = strtotime($duedate) - strtotime($date); 
                $days = abs(round($diffInSeconds / 86400));
                $duration2 = $days+1;
                if ($duration != $duration2) {
                    $duration = $duration2;
                }
                
                $task = Task::create([
                    'row_index' => $key,
                    'project_id' => $projectId,
                    'item_type' => strtolower($row[0]),
                    'name' => $row[1],
                    'duration' => $duration,
                    'start' => $date . ' 00:00',
                    'duedate' => $duedate . ' 00:00',
                    'ins' => auth()->user()->ins,
                    'user_id' => auth()->user()->id,
                    'creator_id' => auth()->user()->id,
                ]);
                ++$this->rows;
            }            
        }
    }

    public function rules(): array
    {
        return [];
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
