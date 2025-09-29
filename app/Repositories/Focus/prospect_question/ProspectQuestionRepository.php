<?php

namespace App\Repositories\Focus\prospect_question;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\prospect_question\ProspectQuestion;
use App\Models\prospect_question\ProspectQuestionItem;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProspectQuestionRepository.
 */
class ProspectQuestionRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ProspectQuestion::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get();
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
        DB::beginTransaction();
        $data = $input['data'];
        $result = ProspectQuestion::create($data);

        //Insert items
        $data_items = $input['data_items'];

        $data_items = array_map(function($v) use($result){
            return array_replace($v, [
                'prospect_question_id' => $result->id,
                'ins' => $result->ins
            ]);
        }, $data_items);
        ProspectQuestionItem::insert($data_items);

        if($result){
            DB::commit();
            return $result;
        }

        throw new GeneralException('Error creating Prospect Question');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Department $prospect_question
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($prospect_question, array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        $prospect_question->update($data);

        //update items
        $data_items = $input['data_items'];
        // dd($data_items);
        //remove ommited items
        $item_ids = array_map(function ($v){return $v['id'];}, $data_items);
        $prospect_question->questions()->whereNotIn('id',$item_ids)->delete();

        //create or update new questions
        foreach ($data_items as $item){
            $prospect_question_item = ProspectQuestionItem::firstOrNew(['id' => $item['id']]);
            $prospect_question_item->fill(array_replace($item, ['prospect_question_id' => $prospect_question->id, 'ins' => $prospect_question->ins, 'user_id'=>$prospect_question->user_id]));
            if (!$prospect_question_item->id) unset($prospect_question_item->id);
            $prospect_question_item->save();
        }

        if ($prospect_question){
            DB::commit();
            return true;
        }

        throw new GeneralException("Error updating Prospect Question");
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Department $prospect_question
     * @throws GeneralException
     * @return bool
     */
    public function delete($prospect_question)
    {
       DB::beginTransaction();
       if($prospect_question->delete()){
        DB::commit();
        return true;
       }

        throw new GeneralException('Error deleting Prospect Question');
    }
}
