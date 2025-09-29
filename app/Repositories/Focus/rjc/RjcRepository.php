<?php

namespace App\Repositories\Focus\rjc;

use DB;
use App\Models\items\RjcItem;
use App\Models\rjc\Rjc;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;

/**
 * Class ProductcategoryRepository.
 */
class RjcRepository extends BaseRepository
{
    /**
     *file_path .
     *
     * @var string
     */
    protected $file_path;

    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;

    /**
     * Associated Repository Model.
     */
    const MODEL = Rjc::class;

    public function __construct()
    {
        $this->file_path = 'img' . DIRECTORY_SEPARATOR . 'rjcreport' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * 
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return object
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach($data as $key => $value) {
            if (in_array($key, ['image_one', 'image_two', 'image_three', 'image_four'])) {
                if ($value) $data[$key] = $this->uploadFile($value);
            }
            if ($key == 'report_date') 
                $data[$key] = date_for_database($value);
        }
        // update tid
        $tid =  Rjc::where('ins', $data['ins'])->max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid + 1;

        $result = Rjc::create($data);

        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'rjc_id' => $result->id,
                'ins' => $result->ins,
                'last_service_date' => date_for_database($v['last_service_date']),
                'next_service_date' => date_for_database($v['next_service_date']),
            ]);
        }, $data_items);
        RjcItem::insert($data_items);
        
        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException('Error Creating Rjc');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Rjc $rjc
     * @param  array $input
     * @throws GeneralException
     * @return object
     */
    public function update($rjc, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach($data as $key => $val) {
            if (in_array($key, ['image_one', 'image_two', 'image_three', 'image_four'])) {
                if ($val) $data[$key] = $this->uploadFile($val);
            }
            if ($key == 'report_date') 
                $data[$key] = date_for_database($val);
        }
        $result = $rjc->update($data);

        $data_items = $input['data_items'];
        // delete omitted rjc items
        $item_ids = array_map(fn($v) => $v['item_id'], $data_items);
        $rjc->rjc_items()->whereNotIn('id', $item_ids)->delete();
        // create or update 
        foreach($data_items as $item) {
            $item = array_replace($item, [
                'rjc_id' => $rjc->id, 
                'ins' => $rjc->ins,
                'last_service_date' => date_for_database($item['last_service_date']),
                'next_service_date' => date_for_database($item['next_service_date']),
            ]);
            $new_item = RjcItem::firstOrNew(['id' => $item['item_id']]);
            $new_item->fill($item);
            if (!$new_item->id) unset($new_item->id);
            unset($new_item->item_id);
            $new_item->save();
        }

        if ($result) {
            DB::commit();
            return $rjc;
        }

        throw new GeneralException('Error Updating Rjc');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Rjc $rjc
     * @throws GeneralException
     * @return bool
     */
    public function delete(Rjc $rjc)
    {
        if ($rjc->delete()) return true;
    
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    // Upload file to storage
    public function uploadFile($file)
    {
        $file_name = time() . $file->getClientOriginalName();
        
        $this->storage->put($this->file_path . $file_name, file_get_contents($file->getRealPath()));

        return $file_name;
    }
}
