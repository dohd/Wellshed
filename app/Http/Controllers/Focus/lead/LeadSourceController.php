<?php

namespace App\Http\Controllers\Focus\lead;

use App\Http\Controllers\Controller;
use App\Models\lead\LeadSource;
use Closure;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LeadSourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {

            $leadSources = LeadSource::all();

            return Datatables::of($leadSources)
                ->addColumn('action', function ($model) {

                    $route = route('biller.lead-sources.edit', $model->id);
                    $routeShow = route('biller.lead-sources.show', $model->id);
                    $routeDelete = route('biller.lead-sources.destroy', $model->id);

                    return '<a href="'.$route.'" class="btn btn-secondary round mr-1">Edit</a>'
                        . '<a href="' .$routeDelete . '" 
                            class="btn btn-danger round" data-method="delete"
                            data-trans-button-cancel="' . trans('buttons.general.cancel') . '"
                            data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '"
                            data-trans-title="' . trans('strings.backend.general.are_you_sure') . '" 
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="Delete"
                            >
                                <i  class="fa fa-trash"></i>
                            </a>';

                })
                ->rawColumns(['action'])
                ->make(true);

        }


        return view('focus.lead_sources.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('focus.lead_sources.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $request->validate([
            'name' => ['required',

                function (string $attribute, $value, Closure $fail) {

                    $existingName = LeadSource::where('name', $value)->first();

                    if ($existingName) $fail('Action Denied! You already have a source by this name');
                },
            ],
        ]);

//        return $request;

        $ls = new LeadSource();
        $ls->name = $request->name;
        $ls->ins = auth()->user()->ins;
        $ls->save();

        return redirect()->route('biller.lead-sources.index')
            ->with('success', 'Lead source created successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $leadSource = LeadSource::find($id);

        return view('focus.lead_sources.edit', compact('leadSource'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $leadSource = LeadSource::find($id);

        $request->validate([
            'name' => ['required',

                function (string $attribute, $value, Closure $fail) use ($id) {

                    $existingName = LeadSource::where('id' != $id)->where('name', $value)->first();

                    if ($existingName) $fail('Action Denied! You already have a source by this name');
                },
            ],
        ]);

        $leadSource = LeadSource::find($id);
        $leadSource->update($request->all());

        return redirect()->route('biller.lead-sources.index')
            ->with('success', 'Lead source updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        $leadSource = LeadSource::find($id);

        if (count($leadSource->leads) > 0){
            return redirect()->route('biller.lead-sources.index')
                ->with('flash_error', 'Delete Blocked as Source is Allocated to ' . count($leadSource->leads) . ' tickets');
        }
        else if (count($leadSource->leads) > 0){
            return redirect()->route('biller.lead-sources.index')
                ->with('flash_error', 'Delete Blocked as Source is Allocated to ' . count($leadSource->leads) . ' tickets');
        }

        $leadSource->delete();

        return redirect()->route('biller.lead-sources.index')
            ->with('success', 'Ticket source deleted successfully');
    }
}
