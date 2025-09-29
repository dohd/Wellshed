<?php

namespace App\Repositories\Focus\tenant_service;

use App\Exceptions\GeneralException;
use App\Models\package\TenantServicePackage;
use App\Models\tenant_service\TenantService;
use App\Models\tenant_service\TenantServiceItem;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;

/**
 * Class ProductcategoryRepository.
 */
class TenantServiceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TenantService::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        $q = $this->query()->whereNull('deleted_at');

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
        DB::beginTransaction();

        foreach ($input as $key => $value) {
            $keys = ['cost', 'maintenance_cost', 'total_cost', 'extras_total', 'extra_cost', 'maint_cost'];
            if (in_array($key, $keys)) {
                if (is_array($value)) {
                    $input[$key] = array_map(fn($v) => numberClean($v), $value);
                } else {
                    $input[$key] = numberClean($value);
                }
            }
            if ($key == 'module_id') $input[$key] = implode(',', $value);
        } 
        $service = TenantService::create($input);

        $tsp = new TenantServicePackage();
        $tsp->tsp_number = uniqid('TSP-', true);
        $tsp->tenant_service_id = $service->id;
        $tsp->package_number = $input['package_number'];
        $tsp->save();

        $items_data = Arr::only($input, ['package_id', 'extra_cost', 'maint_cost']);
        $items_data = modify_array($items_data);
        foreach ($items_data as $key => $value) {
            $items_data[$key]['tenant_service_id'] = $service->id; 
        }
        TenantServiceItem::insert($items_data);

        if ($service) {
            DB::commit();
            return $service;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param TenantService $tenant_service
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(TenantService $tenant_service, array $input)
    {   
        DB::beginTransaction();
        
        foreach ($input as $key => $value) {
            $keys = ['cost', 'maintenance_cost', 'total_cost', 'extras_total', 'extra_cost', 'maint_cost'];
            if (in_array($key, $keys)) {
                if (is_array($value)) {
                    $input[$key] = array_map(fn($v) => numberClean($v), $value);
                } else {
                    $input[$key] = numberClean($value);
                }
            }
            if ($key == 'module_id') $input[$key] = implode(',', $value);
        }
        
        $result = $tenant_service->update($input);
        $tenant_service->items()->delete();

        $existingTsp = TenantServicePackage::where('tenant_service_id', $tenant_service->id)->first();

        $tsp = $existingTsp ?? new TenantServicePackage();
        if (!$existingTsp) $tsp->tsp_number = uniqid('TSP-', true);

        $tsp->tenant_service_id = $tenant_service->id;
        $tsp->package_number = $input['package_number'];
        $tsp->save();


        $items_data = Arr::only($input, ['package_id', 'extra_cost', 'maint_cost']);
        $items_data = modify_array($items_data);
        foreach ($items_data as $key => $value) {
            $items_data[$key]['tenant_service_id'] = $tenant_service->id; 
        }
        TenantServiceItem::insert($items_data);

        if ($result) {
            DB::commit();
            return true;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param TenantService $tenant_service
     * @throws GeneralException
     * @return bool
     */
    public function delete(TenantService $tenant_service)
    {  
        // $tenant_service->items()->delete();
        // $tenant_service->delete();
        return $tenant_service->update(['deleted_at' => now()]);
    }
}
