<?php

namespace App\Repositories\Focus\casual;

use DB;
use Carbon\Carbon;
use App\Models\casual\CasualLabourer;
use App\Exceptions\GeneralException;
use App\Models\casual\CasualDoc;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Storage;

/**
 * Class CasualRepository.
 */
class CasualRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = CasualLabourer::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        return $q->get();
    }

    public function uploadFile($file)
    {
        $fileName = time() . '-' . $file->getClientOriginalName();
        $filePath = 'files' . DIRECTORY_SEPARATOR . 'casual_docs' . DIRECTORY_SEPARATOR;
        Storage::disk('public')->put($filePath . $fileName, file_get_contents($file->getRealPath()));
        
        return $fileName;
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
        $data = $input['data'];
        $data['rate'] = numberClean($data['rate']);
        $wageItemIds = @$data['wage_item_id'];
        unset($data['wage_item_id']);

        DB::beginTransaction();

        // create casual
        $casual = CasualLabourer::create($data);
        $casual->wageItems()->attach($wageItemIds);

        // create casual items
        $document_data = $input['data_items'];
        $document_data = array_filter($document_data, fn($v) => @$v['document_name']);
        $document_data = array_map(function ($v) use($casual) {
            $v1 = [
                'casual_labourer_id' => $casual->id,
                'caption' => $v['caption'],
                'ins' => $casual->ins,
            ];
            // fail safe for upload
            try {
                $v1['document_name'] = $this->uploadFile($v['document_name']);
            } catch (\Exception $e) {dd($e);
                // 
            }
            return $v1;
        }, $document_data);
        $document_data = array_filter($document_data, fn($v) => $v['caption']);
        CasualDoc::insert($document_data);
        
        DB::commit();
        return $casual;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param CasualLabourer $CasualLabourer
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($casual, array $input)
    {
    	$data = $input['data'];
        $data['rate'] = numberClean($data['rate']);
        $wageItemIds = @$data['wage_item_id'];
        unset($data['wage_item_id']);

        DB::beginTransaction();

        // update casual
        $result = $casual->update($data);
        $casual->wageItems()->sync($wageItemIds);

        $document_data = $input['data_items'];
        // Delete omitted documents (documents not in request)
        $doc_ids = array_map(fn($v) => $v['doc_id'], $document_data);
        $casual->casual_docs()->whereNotIn('id', $doc_ids)->delete();
        // Create or update document items
        foreach ($document_data as $item) {
            $new_item = CasualDoc::firstOrNew(['id' => $item['doc_id'] != "0" ? $item['doc_id'] : null]);
            // Check if a new file is uploaded
            if (isset($item['document_name']) && $item['document_name'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old file if exists
                if (!empty($item['existing_document_name'])) {
                    Storage::disk('public')->delete('casuals/' . $item['existing_document_name']);
                }
                // Upload new file
                $new_item->document_name = $this->uploadFile($item['document_name']);
            } else {
                // Retain old document if no new file is uploaded
                $new_item->document_name = $item['existing_document_name'] ?? null;
            }
            // Assign other attributes
            $new_item->fill([
                'casual_labourer_id' => $casual->id,
                'caption' => $item['caption'],
            ]);
            // If it's a new document (doc_id == 0), assign user details
            if (!$new_item->id) {
                // $new_item->user_id = auth()->user()->id;
                $new_item->ins = auth()->user()->ins;
            }
            $new_item->save();
        }

        DB::commit();
        return $result;
    }

    /**
     * For deleting the respective model from storage
     *
     * @param CasualLabourer $CasualLabourer
     * @throws GeneralException
     * @return bool
     */
    public function delete($casual)
    {
        DB::beginTransaction();
        $casual->casual_docs()->delete();
        $casual->wageItems()->detach();
        if ($casual->delete()) {
            DB::commit();
            return true;
        }
    }
}
