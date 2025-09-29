<?php

namespace App\Http\Controllers\Focus\employeeNotice;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\employeeNotice\EmployeeNotice;
use App\Models\hrm\Hrm;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class EmployeeNoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (!access()->allow('manage-employee-notice')) return redirect()->back();

        $employees = Hrm::orderBy('first_name')->select('id', 'first_name', 'last_name')->get();

        if ($request->ajax()) {

            $notice = EmployeeNotice::when(request('employeeFilter'), function ($q) {
                    $q->where('employee_id', intval(request('employeeFilter')));
                })
                ->get();

            return Datatables::of($notice)

                ->addColumn('employee', function ($notice) {

                    $emp = Hrm::withoutGlobalScopes(['status'])->find($notice->employee_id);

                    return $emp->first_name . ' ' . $emp->last_name;
                })

                ->editColumn('date', function ($notice) {


                    return (new DateTime($notice->date))->format('d/m/Y');
                })

                ->addColumn('document', function ($notice) {

                    $download = '<a href="' . route('biller.employee-notice.download', $notice->id) . '" class="btn btn-success">Download</a>';;

                    return empty($notice->document_path) ? '' : $download;
                })

                ->addColumn('action', function ($notice) {

                    $view = '<a href="' . route('biller.employee-notice.show', $notice->id) . '" class="btn btn-twitter round mr-1">View</a>';

                    $edit = '<a href="' . route('biller.employee-notice.edit', $notice->id) . '" class="btn btn-secondary round mr-1">Edit</a>';

                    $delete = '<form action="' . route('biller.employee-notice.destroy', $notice->id) . '" method="POST" style="display:inline-block;">' .
                        csrf_field() .
                        method_field("DELETE") .
                        '<button type="submit" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this document?\')">Delete</button>' .
                        '</form>';

                    if (!access()->allow('view-employee-notice')) $view = '';
                    if (!access()->allow('edit-employee-notice')) $edit = '';
                    if (!access()->allow('delete-employee-notice')) $delete = '';

                    return $view . $edit . $delete;
                })
                ->rawColumns(['action', 'document', 'content'])
                ->make(true);
        }

        return view('focus.employeeNotice.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!access()->allow('create-employee-notice')) return redirect()->back();

        $employees = Hrm::select('id', 'first_name', 'last_name')->get();

        return view('focus.employeeNotice.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!access()->allow('create-employee-notice')) return redirect()->back();

        $validated = $request->validate([
            'employee_id' => ['required', 'integer'],
            'title' => ['required'],
            'date' => ['required', 'date'],
            'file' => ['nullable', 'max:10000'],
            'content' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            $uniqueFileName = uniqid(pathinfo(str_replace(' ' , '', $originalName), PATHINFO_FILENAME) . '-', true) . '.' . $file->getClientOriginalExtension();

            $filePath = $file->storeAs('employeeNotices', $uniqueFileName, 'public');

            $employeeNotice = new EmployeeNotice();
            $employeeNotice->fill($validated);
            $employeeNotice->document_path = $filePath;
            $employeeNotice->save();

            DB::commit();
        }
        catch (Exception $ex) {

            DB::rollBack();

            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];


            return errorHandler('Error Updating Direct Purchase', $th);
        }

        return new RedirectResponse(route('biller.employee-notice.index'), ['flash_success' => "Employee Notice saved successfully"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!access()->allow('view-employee-notice')) return redirect()->back();

        $employeeNotice = EmployeeNotice::find($id);
        $employees = Hrm::select('id', 'first_name', 'last_name')->get();

        return view('focus.employeeNotice.show', compact('employeeNotice', 'employees'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if (!access()->allow('edit-employee-notice')) return redirect()->back();

        $employeeNotice = EmployeeNotice::find($id);
        $employees = Hrm::select('id', 'first_name', 'last_name')->get();

        return view('focus.employeeNotice.edit', compact('employeeNotice', 'employees'));
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

        if (!access()->allow('edit-employee-notice')) return redirect()->back();

        $validated = $request->validate([
            'title' => ['required'],
            'date' => ['required', 'date'],
            'file' => ['nullable', 'max:10000'],
            'content' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            $uniqueFileName = uniqid(pathinfo(str_replace(' ' , '', $originalName), PATHINFO_FILENAME) . '-', true) . '.' . $file->getClientOriginalExtension();

            $filePath = $file->storeAs('employeeNotices', $uniqueFileName, 'public');

            $employeeNotice = EmployeeNotice::find($id);
            $employeeNotice->fill($validated);
            $employeeNotice->document_path = $filePath;
            $employeeNotice->save();

            DB::commit();
        }
        catch (Exception $ex) {

            DB::rollBack();

            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];


            return errorHandler('Error Updating Direct Purchase', $th);
        }

        return new RedirectResponse(route('biller.employee-notice.index'), ['flash_success' => "Employee Notice saved successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if (!access()->allow('delete-employee-notice')) return redirect()->back();

        $not = EmployeeNotice::find($id);

        $not->delete();

        return new RedirectResponse(route('biller.employee-notice.index'), ['flash_success' => "Employee Notice Deleted successfully"]);
    }

    public function download($id)
    {

        $employeeNotice = EmployeeNotice::find($id);

        return Storage::disk('public')->download($employeeNotice->document_path);
    }

}
