<?php

namespace App\Http\Controllers\Focus\documentManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\documentManager\StoreDocumentManagerRequest;
use App\Http\Requests\Focus\documentManager\UpdateDocumentManagerRequest;
use App\Http\Responses\RedirectResponse;
use App\Models\documentManager\DocumentManager;
use App\Models\hrm\Hrm;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DocumentManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (!access()->allow('manage-document-tracker')) return redirect()->back();

        $documentManager = DocumentManager::with(['responsibleUser', 'responsibleUser'])->get();

        if ($request->ajax()){

            try {

                return Datatables::of($documentManager)

                    ->editColumn('responsible', function ($model) {

                        return $model->responsibleUser->first_name . " " . $model->responsibleUser->last_name ;
                    })

                    ->editColumn('co_responsible', function ($model) {

                        return $model->coResponsibleUser->first_name . " " . $model->coResponsibleUser->last_name ;
                    })

                    ->editColumn('issue_date', function ($model) {

                        return (new DateTime($model->issue_date))->format('jS F Y');
                    })

                    ->editColumn('renewal_date', function ($model) {

                        return (new DateTime($model->renewal_date))->format('jS F Y');
                    })

                    ->editColumn('expiry_date', function ($model) {

                        return (new DateTime($model->expiry_date))->format('jS F Y');
                    })

                    ->editColumn('cost_of_renewal', function ($model) {

                        return numberFormat($model->cost_of_renewal);
                    })

                    ->editColumn('alert_days_before', function ($model) {

                        return $model->alert_days_before . ' Days';
                    })

                    ->editColumn('status', function ($model) {
                        if ($model->status == 'ACTIVE') {
                            return '<div class="round" style="padding: 8px; color: white; background-color: #3fd316; text-align: center;"> Active </div>';
                        } else if ($model->status == 'ARCHIVED') {
                            return '<div class="round" style="padding: 8px; color: white; background-color: #00B5B8; text-align: center;"> Archived </div>';
                        } else {
                            return '<div class="round" style="padding: 8px; color: white; background-color: #b80000; text-align: center;"> EXPIRED </div>';
                        }
                    })

                    ->addColumn('action', function ($model) {

                        $edit = ' <a href="' . route('biller.document-tracker.edit',$model->id) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil"></i></a> ';
                        $delete = '<button class="btn btn-danger trash-document-tracker" id="' . $model->id . '"> <i  class="fa fa-trash"></i> Delete</button>';

                    if(!access()->allow('edit-document-tracker')) $edit = '';
                    if(!access()->allow('delete-document-tracker')) $delete = '';

                        return $edit . $delete;
                    })
                    ->rawColumns(['action', 'status'])
                    ->make(true);

            }
            catch(Exception $exception) {

                return errorHandler('Error Loading Document Tracker Table', $exception);
//                return [
//                    'message' => $exception->getMessage(),
//                    'code' => $exception->getCode(),
//                    'file' => $exception->getFile(),
//                    'line' => $exception->getLine(),
//                ];
            }
        }


        return view('focus.documentManager.index', compact('documentManager'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (!access()->allow('create-document-tracker')) return redirect()->back();

        $employees = Hrm::select(['id', 'first_name', 'last_name'])
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                ];
            });

        $documentTypes = ['LICENSE', 'CONTRACT', 'CERTIFICATE', 'POLICY', 'AGREEMENT'];
        $documentStatuses = ['ACTIVE', 'ARCHIVED', 'EXPIRED'];

        return view('focus.documentManager.create', compact('employees', 'documentTypes', 'documentStatuses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDocumentManagerRequest $request)
    {

        $validated = $request->validated();

        $existingName = DocumentManager::where('name', $validated['name'])->first();

        if(!empty($existingName))
            return redirect()->back()->with('flash_error', "Action Denied. You are Already Tracking a Document by This Name");

        try{
            DB::beginTransaction();

            $documentManager = new DocumentManager();
            $documentManager->fill($validated);
            $documentManager->save();

            DB::commit();
        }
        catch (Exception $exception){

            DB::rollBack();


            return errorHandler('Error Savung Document Tracker', $exception);
//            return [
//                'message' => $exception->getMessage(),
//                'code' => $exception->getCode(),
//                'file' => $exception->getFile(),
//                'line' => $exception->getLine(),
//            ];
        }

        return new RedirectResponse(route('biller.document-tracker.index'), ['flash_success' => 'Document Tracker Saved successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if (!access()->allow('edit-document-tracker')) return redirect()->back();

        $documentManager = DocumentManager::find($id);
        $employees = Hrm::select(['id', 'first_name', 'last_name'])
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                ];
            });

        $documentTypes = ['LICENSE', 'CONTRACT', 'CERTIFICATE', 'POLICY', 'AGREEMENT'];
        $documentStatuses = ['ACTIVE', 'ARCHIVED', 'EXPIRED'];


        return view('focus.documentManager.edit', compact('documentManager', 'employees', 'documentTypes', 'documentStatuses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocumentManagerRequest $request, $id)
    {

        $validated = $request->validated();

        $existingName = DocumentManager::where('id', '!=', $id)->where('name', $validated['name'])->first();

        if(!empty($existingName))
            return redirect()->back()->with('flash_error', "Action Denied. You are Already Tracking a Document by This Name");

        try{
            DB::beginTransaction();

            $documentManager = DocumentManager::find($id);
            $documentManager->fill($validated);
            $documentManager->save();

            DB::commit();
        }
        catch (Exception $exception){

            DB::rollBack();
//            return [
//                'message' => $exception->getMessage(),
//                'code' => $exception->getCode(),
//                'file' => $exception->getFile(),
//                'line' => $exception->getLine(),
//            ];
            return errorHandler('Error Updating Document Tracker', $exception);
        }

        return new RedirectResponse(route('biller.document-tracker.index'), ['flash_success' => 'Document Tracker Updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if (!access()->allow('delete-document-tracker')) return redirect()->back();

        $documentManager = DocumentManager::find($id);
        
        if($documentManager->status !== 'ARCHIVED')
            return redirect()->back()->with('flash_error', "Action Denied. To delete this Document Tracking you have to first set its status to 'Archived'");

        $documentManager->delete();

        return new RedirectResponse(route('biller.document-tracker.index'), ['flash_success' => 'Document Tracker Deleted']);
    }
}
