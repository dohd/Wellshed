<?php

namespace App\Repositories\Focus\productvariable;

use App\Models\productvariable\Productvariable;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductvariableRepository.
 */
class ProductvariableRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Productvariable::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        return $this->query()->get();
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
        $input['base_ratio'] = numberClean($input['base_ratio']);

        $params = ['title' => $input['title'], 'code' => $input['code']];
        $exists = Productvariable::where('unit_type', 'base')->where($params)->count();
        if ($exists) throw ValidationException::withMessages(['Base Unit exists!']);

        $result = Productvariable::create($input);
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.productvariables.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param App\Models\productvariable\Productvariable $productvariable
     * @param  array $input
     * @throws GeneralException
     * @return bool
     */
    public function update($productvariable, array $input)
    {
        // dd($input);
        $input['base_ratio'] = numberClean($input['base_ratio']);

        $unitExists = Productvariable::where('id', '!=', $productvariable->id)
            ->where('unit_type', 'base')
            ->where(['title' => $input['title'], 'code' => $input['code']])
            ->exists();
        if ($unitExists) throw ValidationException::withMessages(['Base Unit exists!']);

        $result = $productvariable->update($input);
    	return $result;
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productvariable $productvariable
     * @throws GeneralException
     * @return bool
     */
    public function delete($productvariable)
    {
        if ($productvariable->products()->exists()) throw ValidationException::withMessages(['Unit of Measure has attached Products']);
        if ($productvariable->unit_type == 'base') {
            $rel_units = Productvariable::where(['unit_type' => 'compound', 'base_unit_id' => $productvariable->id])->count();
            if ($rel_units) throw ValidationException::withMessages(['Unit is attached to related compound unit']);
        }

        if ($productvariable->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.productvariables.delete_error'));
    }
}
