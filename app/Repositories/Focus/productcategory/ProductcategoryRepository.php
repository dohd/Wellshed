<?php

namespace App\Repositories\Focus\productcategory;

use App\Models\productcategory\Productcategory;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class ProductcategoryRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Productcategory::class;

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

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        $input = array_map( 'strip_tags', $input);
        $codeExists = Productcategory::where('code_initials', $input['code_initials'])->exists();
        if ($codeExists) throw ValidationException::withMessages(['Code initials exists']);

        // dd($input);
        if($input['child_id'] > 0){
            $category = Productcategory::find($input['child_id']);
            $input['rel_id'] = $category->rel_id;
        }
       $c=Productcategory::create($input);
       if ($c->id) return $c->id;
        throw new GeneralException(trans('exceptions.backend.productcategories.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Productcategory $productcategory, array $input)
    {
        $input = array_map( 'strip_tags', $input);
        $codeExists = Productcategory::where('code_initials', $input['code_initials'])->exists();
        if($codeExists && $productcategory->code_initials != $input['code_initials']){
            throw ValidationException::withMessages(['Code Initials Exists!!']);
        }
        return $productcategory->update($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Productcategory $productcategory)
    {
        if ($productcategory->products()->exists()) {
            throw ValidationException::withMessages(['ProductCategory is attached to Inventory Products!']);
        }

        return $productcategory->delete();
    }
}
