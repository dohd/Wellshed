<?php

namespace App\Http\Controllers\Focus\template_quote;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\template_quote\TemplateQuoteRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\quote\QuoteRepository;
use DB;
class TemplateQuoteTableController extends Controller
{
    protected $repository;

    public function __construct(TemplateQuoteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke()
    {
        $query = $this->repository->getForDataTable();


        $ins = auth()->user()->ins;

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('notes', function ($quote) {
                return $quote->notes;
            })
            ->addColumn('actions', function ($term) {
                return $term->action_buttons;
            })
            ->make(true);
    }
}
