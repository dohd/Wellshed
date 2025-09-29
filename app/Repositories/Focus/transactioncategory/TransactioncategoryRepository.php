<?php

namespace App\Repositories\Focus\transactioncategory;

use App\Models\Company\ConfigMeta;
use App\Models\transactioncategory\Transactioncategory;
use App\Exceptions\GeneralException;
use App\Models\transaction\Transaction;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class TransactioncategoryRepository.
 */
class TransactioncategoryRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Transactioncategory::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get(['id', 'name', 'note', 'sub_category', 'sub_category_id', 'created_at']);
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
        $input = array_map('strip_tags', $input);
        $tr_category = Transactioncategory::create($input);
        return $tr_category;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Transactioncategory $transactioncategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Transactioncategory $transactioncategory, array $input)
    {
        $input = array_map('strip_tags', $input);
        $input['code'] = $transactioncategory->code;
        if (auth()->user()->ins == 1) $result = $transactioncategory->update($input);
        else $result = Transactioncategory::create($input);
        return $result;
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Transactioncategory $transactioncategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Transactioncategory $transactioncategory)
    {
        $has_tr_category = Transaction::where('trans_category_id', $transactioncategory->id)->exists();
        if ($has_tr_category) throw ValidationException::withMessages(['Transaction Category has linked transactions']);

        $features = ConfigMeta::whereIn('feature_id', [8,10])->get();
        foreach ($features as $key => $feature) {
            if ($transactioncategory->id == $feature->feature_value)
                return false;
        }
        return $transactioncategory->delete();
    }
}
