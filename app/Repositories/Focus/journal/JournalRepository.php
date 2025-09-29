<?php

namespace App\Repositories\Focus\journal;

use DB;
use App\Exceptions\GeneralException;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class JournalRepository.
 */
class JournalRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = Journal::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('start_date') && request('end_date'), function ($q) {
            $dates = array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]);
            $q->whereBetween('date', $dates);
        });
       
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {  
        DB::beginTransaction();

        $data = $input['data'];
        $data = array_replace($data, [
            'date' => date_for_database($data['date']),
            'debit_ttl' => numberClean($data['debit_ttl']),
            'credit_ttl' => numberClean($data['credit_ttl'])
        ]);
        $result = Journal::create($data);

        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'journal_id' => $result->id,
                'debit' =>  numberClean($v['debit']),
                'credit' => numberClean($v['credit']),
            ]);
        }, $data_items);
        $data_items = array_filter($data_items, fn($v) => @$v['journal_id'] && ($v['debit'] || $v['credit']));
        if (!$data_items) throw ValidationException::withMessages(['Required fields! debit, credit entries']);
        JournalItem::insert($data_items);
        
        /** accounting */ 
        $this->post_journal_entry($result);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function update($journal, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        $data = array_replace($data, [
            'date' => date_for_database($data['date']),
            'debit_ttl' => numberClean($data['debit_ttl']),
            'credit_ttl' => numberClean($data['credit_ttl'])
        ]);
        $result = $journal->update($data);

        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($journal) {
            return array_replace($v, [
                'journal_id' => $journal->id,
                'debit' =>  numberClean($v['debit']),
                'credit' => numberClean($v['credit']),
            ]);
        }, $data_items);
        $data_items = array_filter($data_items, fn($v) => @$v['journal_id'] && ($v['debit'] || $v['credit']));
        if (!$data_items) throw ValidationException::withMessages(['Required fields! debit, credit entries']);
        $journal->items()->delete();
        JournalItem::insert($data_items);
        
        /** accounting */ 
        $journal->transactions()->delete();
        $this->post_journal_entry($journal);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * Delete method from storage
     */
    public function delete($journal)
    {
        DB::beginTransaction();
        $journal->transactions()->delete();
        $journal->items()->delete(); 
        if ($journal->delete()) {
            DB::commit();
            return true;
        }
    }
}