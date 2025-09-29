<?php

namespace App\Repositories\Focus\contract;

use App\Exceptions\GeneralException;
use App\Models\contract\Contract;
use App\Models\contract\PMDocument;
use App\Models\contract_equipment\ContractEquipment;
use App\Models\task_schedule\TaskSchedule;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Validation\ValidationException;
use Storage;

/**
 * Class ProductcategoryRepository.
 */
class ContractRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Contract::class;

    /**
     *file_path .
     * @var string
     */
    protected $file_path = 'img' . DIRECTORY_SEPARATOR . 'pm_documents' . DIRECTORY_SEPARATOR;

    /**
     * Storage Class Object.
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;

    /**
     * Constructor to initialize class objects
     */
    public function __construct()
    {
        $this->storage = Storage::disk('public');
    }
    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        // customer-login filter
        $customer_id = auth()->user()->customer_id;
        $q->when($customer_id, fn($q) => $q->where('customer_id', $customer_id)); 

        return $q;
    }

    public function getForTaskScheduleDataTable()
    {
        return TaskSchedule::all();
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
        // dd($input);
        DB::beginTransaction();

        $contract_data = $input['contract_data'];
        foreach ($contract_data as $key => $val) {
            if ($key == 'amount') $contract_data[$key] = numberClean($val);
            if (in_array($key, ['start_date', 'end_date'])) 
                $contract_data[$key] = date_for_database($val);
        }
        $result = Contract::create($contract_data);

        $schedule_data = $input['schedule_data'];
        if (!$schedule_data) throw ValidationException::withMessages(['task schedules required!']);

        $schedule_data = array_map(function ($v) use($result) {
            return [
                'contract_id' => $result->id,
                'title' => $v['s_title'],
                'start_date' => date_for_database($v['s_start_date']),
                'end_date' => date_for_database($v['s_end_date']),
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ];
        }, $schedule_data);
        TaskSchedule::insert($schedule_data);

        $document_data = $input['document_data'];
        $document_data = array_map(function ($v) use($result) {
            return [
                'contract_id' => $result->id,
                'caption' => $v['caption'],
                'contract_doc' => $this->uploadFile($v['contract_doc']),
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ];
        }, $document_data);
        PMDocument::insert($document_data);

        $equipment_data = $input['equipment_data'];
        if (!$equipment_data) throw ValidationException::withMessages(['equipments required!']);

        $equipment_data = array_map(function ($v) use($result) {
            return $v + ['contract_id' => $result->id];
        }, $equipment_data);
        ContractEquipment::insert($equipment_data);
        
        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException('Error Creating Contract');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($contract, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $contract_data = $input['contract_data'];
        foreach ($contract_data as $key => $val) {
            if ($key == 'amount') $contract_data[$key] = numberClean($val);
            if (in_array($key, ['start_date', 'end_date'])) 
                $contract_data[$key] = date_for_database($val);
        }
        $result = $contract->update($contract_data);

        $schedule_data = $input['schedule_data'];        
        if (!$schedule_data) throw ValidationException::withMessages(['task schedules required!']);

        $item_ids = array_map(fn($v) => $v['s_id'], $schedule_data);
        // delete omitted schedules
        $contract->task_schedules()->whereNotIn('id', $item_ids)->where('status', 'pending')->delete();
        // create or update schedule item
        foreach ($schedule_data as $item) {
            $new_item = TaskSchedule::firstOrNew(['id' => $item['s_id']]);
            $new_item->fill([
                'contract_id' => $contract->id,
                'title' => $item['s_title'],
                'start_date' => date_for_database($item['s_start_date']),
                'end_date' => date_for_database($item['s_end_date'])
            ]);
            if (!$new_item->id) {
                $new_item['user_id'] = auth()->user()->id;
                $new_item['ins'] = auth()->user()->ins;
                unset($new_item->id);
            }
            $new_item->save();
        }

        $document_data = $input['document_data'];
        $doc_ids = array_map(fn($v) => $v['doc_id'], $document_data);

        // Delete omitted documents (documents not in request)
        $contract->pm_docs()->whereNotIn('id', $doc_ids)->delete();

        // Create or update document items
        foreach ($document_data as $item) {
            $new_item = PMDocument::firstOrNew(['id' => $item['doc_id'] != "0" ? $item['doc_id'] : null]);

            // Check if a new file is uploaded
            if (isset($item['contract_doc']) && $item['contract_doc'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old file if exists
                if (!empty($item['existing_contract_doc'])) {
                    Storage::disk('public')->delete('contracts/' . $item['existing_contract_doc']);
                }

                // Upload new file
            
                $new_item->contract_doc = $this->uploadFile($item['contract_doc']);
            } else {
                // Retain old document if no new file is uploaded
                $new_item->contract_doc = $item['existing_contract_doc'] ?? null;
            }

            // Assign other attributes
            $new_item->fill([
                'contract_id' => $contract->id,
                'caption' => $item['caption'],
            ]);

            // If it's a new document (doc_id == 0), assign user details
            if (!$new_item->id) {
                $new_item->user_id = auth()->user()->id;
                $new_item->ins = auth()->user()->ins;
            }

            $new_item->save();
        }


        $equipment_data = $input['equipment_data'];  
        if (!$equipment_data) throw ValidationException::withMessages(['equipments required!']);

        $item_ids = array_map(fn($v) => $v['contracteq_id'], $equipment_data);
        // delete omitted equipment items
        $contract->contract_equipments()->whereNotIn('id', $item_ids)->delete();
        // create or update equipment items
        foreach ($equipment_data as $item) {
            $new_item = ContractEquipment::firstOrNew(['id' => $item['contracteq_id']]);
            $new_item->fill([
                'contract_id' => $contract->id, 
                'equipment_id' => $item['equipment_id']
            ]);
            $new_item->save();
        }

        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * Add Contract Equipment
     */
    public function add_equipment(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $result = ContractEquipment::insert($input);
        
        if ($result) {
            DB::commit();
            return true;
        }
        
        throw new GeneralException('Error Creating Contract');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($contract)
    {   
        foreach ($contract->task_schedules as $schedule) {
            if ($schedule->equipments->count()) 
                throw ValidationException::withMessages(["Contract Schedule {$schedule->title} has equipments!"]);
        }

        $contract->pm_docs()->delete();
        if ($contract->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
    public function uploadFile($file)
    {
        $file_name = time() . $file->getClientOriginalName();

        $this->storage->put($this->file_path . $file_name, file_get_contents($file->getRealPath()));

        return $file_name;
    }
}