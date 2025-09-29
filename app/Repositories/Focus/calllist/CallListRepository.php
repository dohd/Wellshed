<?php

namespace App\Repositories\Focus\calllist;

use App\Http\Controllers\Focus\prospect_call_list\ProspectCallListController;
use App\Models\prospect\Prospect;
use App\Models\calllist\CallList;
use App\Exceptions\GeneralException;
use App\Models\items\Prefix;
use App\Models\prospect_calllist\ProspectCallList;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\prospect_call_list\ProspectCallListRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class CallListRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = CallList::class;


    private $prospectcalllist;

  

    
    public function __construct()
    {
        
    
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        $q->when(request('user_id'), function($q){
            $q->where('employee_id', request('user_id'));
        });
        $q->when(request('title'), function($q){
            $q->where('title', request('title'));
        });
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $data)
    {
        $data['start_date'] = date_for_database($data['start_date']);
        $data['end_date'] = date_for_database($data['end_date']);
        DB::beginTransaction();
        $res = CallList::create($data);
        // $response = $result->refresh();
        
        // return $response;
        $restitle = $res['title'];
        $restitle = strstr($restitle, ' ', true);
        $title = Str::before($res['title'], ' -');
       
        //get call id
        $callid = $res['id'];
        //get prospects based on title
        //New prospects
        if($res->category == 'direct'){
            $prospects = Prospect::where('call_status','notcalled')->where('category','direct')
            ->whereDoesntHave('prospectcalllist')->take($res['prospects_number'])
            ->get([
                "id"
            ])->toArray();
        }else{
            $prospects = Prospect::where('call_status','notcalled')->where('category','excel')->where('title',$title)
        ->whereDoesntHave('prospectcalllist')->take($res['prospects_number'])
        ->get([
            "id"
        ])->toArray();
        }
        
        // dd($prospects,$res['title'], $title, $res,$res->category);

        //dd($prospects);
        //start and end date  
        $start = $res['start_date'];
        $end = $res['end_date'];
        // Create an empty array to store the valid dates
        $validDates = [];
        $carbonstart = Carbon::parse($start);
        $carbonend = Carbon::parse($end);
        
        // Loop through each date in the range
        for ($date = $carbonstart; $date <= $carbonend; $date->addDay()) {
            // Check if the current date is not a Sunday or Saturday
            if ($date->isWeekday()) {
                // Add the date to the array of valid dates
                $validDates[] = $date->toDateString();
            }
        }
        $prospectcount = count($prospects);
        $dateCount = count($validDates);
        $prospectIndex = 0;
        $dateIndex = 0;

        $prospectcalllist = [];


        // Allocate the prospects to the valid dates

        while ($prospectIndex < $prospectcount && $dateIndex < $dateCount) {
            $prospect = $prospects[$prospectIndex]['id'];
            $date = $validDates[$dateIndex];
            $prospectcalllist[] = [
                "prospect_id" => $prospect,
                "call_id" => $callid,
                "call_date"=>$date,
                "ins" => auth()->user()->ins,
                "user_id" => auth()->user()->id
            ];
            $prospectIndex++;
            $dateIndex = ($dateIndex + 1) % $dateCount;
        }
    
        // dd($prospectcalllist);
        // //send data to prospectcalllisttable
         ProspectCallList::insert($prospectcalllist);
         if($prospectcount > 0){
            DB::commit();
            return $res;
         }

        throw new GeneralException('Error Creating CallList');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\CallList $calllist
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($calllist, array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        $data['start_date'] = date_for_database($data['start_date']);
        $data['end_date'] = date_for_database($data['end_date']);
        $result = $calllist->update($data);

        $start = $calllist['start_date'];
        $end = $calllist['end_date'];
        // Create an empty array to store the valid dates
        $validDates = [];
        $carbonstart = Carbon::parse($start);
        $carbonend = Carbon::parse($end);
        
        // Loop through each date in the range
        for ($date = $carbonstart; $date <= $carbonend; $date->addDay()) {
            // Check if the current date is not a Sunday or Saturday
            if ($date->isWeekday()) {
                // Add the date to the array of valid dates
                $validDates[] = $date->toDateString();
            }
        }
        //call list items
        $items = $calllist->items;
        $prospectcount = count($items);
        $dateCount = count($validDates);
        $prospectIndex = 0;
        $dateIndex = 0;


        // Allocate the prospects to the valid dates

        while ($prospectIndex < $prospectcount) {
            // Update each item directly
            $items[$prospectIndex]->update([
                "call_date" => $validDates[$dateIndex],
            ]);
        
            $prospectIndex++;
            $dateIndex = ($dateIndex + 1) % $dateCount; // Cycle through valid dates
        }

        if ($result) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\calllist\CallList $calllist
     * @throws GeneralException
     * @return bool
     */
    public function delete(CallList $calllist)
    {
       
        $id = $calllist->id;
        $calllistdeleted = $calllist->delete();
        $childrendeleted = ProspectCallList::where('call_id',$id)->delete();
        if ( $childrendeleted){
           
            if( $calllistdeleted) return true;
        } 

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}
