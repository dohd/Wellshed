<?php

namespace App\Http\Controllers\Focus\journal;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\manualjournal\Journal;
use App\Models\project\Project;
use App\Models\supplier\Supplier;
use App\Repositories\Focus\journal\JournalRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JournalsController extends Controller
{
    /**
     * variable to store the repository object
     * @var JournalRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param JournalRepository $repository ;
     */
    public function __construct(JournalRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.journals.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $tid = Journal::max('tid')+1;

        return new ViewResponse('focus.journals.create', compact('tid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only(['tid', 'date', 'note', 'debit_ttl', 'credit_ttl']);
        $data_items = $request->only(['account_id', 'debit', 'credit', 'supplier_id', 'customer_id', 'project_id']);

        $data_items = modify_array($data_items);
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        try {
            $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Journal Entry', $th);
        }

        return new RedirectResponse(route('biller.journals.index'), ['flash_success' => 'Journal Entry created successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Journal $journal
     * @return \Illuminate\Http\Response
     */
    public function edit(Journal $journal)
    {
        return new ViewResponse('focus.journals.edit', compact('journal'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Journal $journal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Journal $journal)
    {
        if ($journal->reconciliation_items()->where('checked', 1)->exists()) 
            return errorHandler('Not Allowed! Journal Entry has been reconciled');

        $data = $request->only(['tid', 'date', 'note', 'debit_ttl', 'credit_ttl']);
        $data_items = $request->only(['account_id', 'debit', 'credit', 'supplier_id', 'customer_id', 'project_id']);

        $data_items = modify_array($data_items);
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        try {
            $this->repository->update($journal, compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Journal Entry', $th);
        }
        
        return new RedirectResponse(route('biller.journals.index'), ['flash_success' => 'Journal Entry Updated Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Journal $journal)
    {
        return new ViewResponse('focus.journals.view', compact('journal'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Journal $journal)
    {
        if ($journal->reconciliation_items()->where('checked', 1)->exists())
            return errorHandler('Not Allowed! Journal Entry has been reconciled');
        // check if is inventory opening balance 
        $inventory_acc = Account::where('id', $journal->account_id)->whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))->first(['id']);
        if ($inventory_acc) return errorHandler('Opening Inventory Balance transaction cannot be deleted!');
        
        try {
            $this->repository->delete($journal);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Journal Entry', $th);
        }

        return new RedirectResponse(route('biller.journals.index'), ['flash_success' => 'Journal Entry deleted successfully']);
    }

    /**
     * Fetch journal accounts
     */
    public function journal_accounts()
    {
        $accounts = Account::where('is_manual_journal', 1)
        ->where(function($q) {
            $q->where('number', 'LIKE', '%'. request('term') .'%')
            ->orWhere('holder', 'LIKE', '%'. request('term') .'%');
        })
        ->with(['accountType' => fn($q) =>  $q->select('id', 'category')->get()])
        ->limit(6)
        ->get();

        return response()->json($accounts);
    }

    /**
     * Supplier and Customer Account Names
     * 
     */
    public function account_names()
    {
        $data = [];
        if (request('is_customer')) {
            $data = Customer::where('ar_account_id', request('account_id'))
            ->where(fn($q) => $q->where('company', 'LIKE', '%' . request('term') . '%')->orWhere('name', 'LIKE', '%' . request('term') . '%'))
            ->limit(6)
            ->get(['id', 'company', 'name']);
        } elseif (request('is_supplier')) {
            $data = Supplier::where('ap_account_id', request('account_id'))
            ->where(fn($q) => $q->where('company', 'LIKE', '%' . request('term') . '%')->orWhere('name', 'LIKE', '%' . request('term') . '%'))
            ->limit(6)
            ->get(['id', 'company', 'name']);
        }
        
        return response()->json($data);
    }


    /**
     * Project autocomplete search
     */
    public function projectSearch(Request $request)
    {
        $kw = $request->term;
        $projects = Project::when(request('is_expense'), fn($q) => $q->whereHas('misc', fn($q) => $q->where('name', '!=', 'Completed')))
            ->when(request('customer_id'), fn($q) => $q->where('customer_id', request('customer_id')))
            ->where(function($q) use($kw) {
                $q->where('tid', 'LIKE', "%{$kw}%")
                ->orWhere('name', 'LIKE', "%{$kw}%")
                ->orWhereHas('branch', fn ($q) => $q->where('name', 'LIKE', "%{$kw}%"))
                ->orWhereHas('customer_project', fn ($q) =>  $q->where('company', 'LIKE', "%{$kw}%"));
            })
            ->limit(6)
            ->orderBy('id','desc')
            ->get(['id', 'tid', 'name'])
            ->map(function($item) {
                $item->tid = gen4tid('PRJ-', $item->tid);
                return $item;
            });

        return response()->json($projects);
    }
}
