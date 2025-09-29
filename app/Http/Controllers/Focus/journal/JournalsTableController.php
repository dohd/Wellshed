<?php

namespace App\Http\Controllers\Focus\journal;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\journal\JournalRepository;
use Yajra\DataTables\Facades\DataTables;

class JournalsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var JournalRepository
     */
    protected $journal;

    /**
     * contructor to initialize repository object
     * @param JournalRepository $journal ;
     */
    public function __construct(JournalRepository $journal)
    {
        $this->journal = $journal;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->journal->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('tid', function ($journal) {
                return gen4tid('JNL-', $journal->tid);
            })
            ->addColumn('date', function ($journal) {
                return dateFormat($journal->date);
            })
            ->addColumn('amount', function ($journal) {
                if ($journal->credit_ttl > 0) {
                    return numberFormat($journal->credit_ttl);
                }
                if ($journal->debit_ttl > 0) {
                    return numberFormat($journal->debit_ttl);
                }
            })
            ->addColumn('actions', function ($journal) {
                return $journal->action_buttons;
            })
            ->make(true);
    }
}