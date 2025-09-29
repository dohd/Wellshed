<?php

namespace App\Http\Controllers\focus\toolkit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\toolkit\ToolkitRepository;
use App\Models\equipmenttoolkit\EquipmentToolKit;

class ToolkitTableController extends Controller
{
     /**
     * variable to store the repository object
     * @var ToolkitRepository
     */
    protected $toolkit;

    /**
     * contructor to initialize repository object
     * @param ToolkitRepository $assetorder ;
     */
    public function __construct(ToolkitRepository $toolkit)
    {
        $this->toolkit = $toolkit;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->toolkit->getForDataTable();

        return Datatables::of($core)
            ->addIndexColumn()
            ->escapeColumns(['id'])
            ->addColumn('id', function ($toolkit) {
                return $toolkit->id;
            })
            ->addColumn('toolkit_name', function ($toolkit) {
                $name = $toolkit->toolkit_name;
                return $name;
            })
            ->addColumn('attached_to', function ($toolkit) {
                $link = [];
                $attached = EquipmentToolKit::where('tool_id',$toolkit->id)->get();
                foreach ($attached as $attached_item) {
                    if($attached_item){
                        $eqId = $attached_item['equipment_id']; 
                        $link[] = '<a href="' . route('biller.equipments.show',$eqId). '">' .'Eq-'. $eqId . '</a>';
                        
                    }
                }

                return  '<br>' . implode(', ', array_unique($link));
            })
            
            ->addColumn('created_at', function ($toolkit) {
                return $toolkit->created_at;
            })
            ->addColumn('actions', function ($toolkit) {
                return $toolkit->action_buttons;
            })
            ->make(true);
    }
}
