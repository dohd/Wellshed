<?php

namespace App\Repositories\Focus\spvariations;

use DB;
use Carbon\Carbon;
use App\Models\pricegroup\PriceGroupVariation;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;
/**
 * Class WarehouseRepository.
 */
class SpVariableRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = PriceGroupVariation::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
       //  ->join('blogs', 'customers.id', '=', 'blogs.id');
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
        
        $pricegroup_id=$input['sprices']['pricegroup_id'];
      
        DB::beginTransaction();
        $products = array();

            //selling price greater than 0
         
            

 

            foreach ($input['sprices']['product_variation_id'] as $key => $value) {
    

   if($input['sprices']['selling_price'][$key]>0){

                  //if project take to work in progress account
                 if(!empty( $input['sprices']['id'][$key])){

                    
                    $stock_update[] = array('id' => $input['sprices']['id'][$key], 'selling_price' => numberClean($input['sprices']['selling_price'][$key]));
                     

                 }else{

                 
                  $products[] = array(
                    'product_variation_id' => $input['sprices']['product_variation_id'][$key],
                    'product_id' => $input['sprices']['product_id'][$key],
                    'pricegroup_id' =>$pricegroup_id,
                    'ins' => $input['sprices']['ins'],
                    'selling_price' => $input['sprices']['selling_price'][$key]    
                );

              }
               }
            }


if($products){

    PriceGroupVariation::insert($products);
}

if($stock_update){

    $update_variation = new PriceGroupVariation;

            $index = 'id';
            Batch::update($update_variation, $stock_update, $index);  
}

            
          

       


            DB::commit();
            return $result;
        
        throw new GeneralException(trans('exceptions.backend.sellingprice.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Warehouse $warehouse
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Pricegroup $pricegroup, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($pricegroup->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.pricegroups.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Warehouse $warehouse
     * @throws GeneralException
     * @return bool
     */
    public function delete(Pricegroup $pricegroup)
    {
        if ($pricegroup->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.pricegroups.delete_error'));
    }
}
