@extends ('core.layouts.app')

@section ('title', 'Manual Journals')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Manual Journals</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.journals.partials.journals-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table id="journalsTbl" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    @php
                        $journal_details = [
                            '#Serial' => $journal->tid,
                            'Date' => dateFormat($journal->date),
                            'Note' => $journal->note,
                            'Total debit' => number_format($journal->debit_ttl, 2),
                            'Total credit' => number_format($journal->credit_ttl, 2),                          
                        ];
                    @endphp
                    @foreach ($journal_details as $key => $val)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>

            <div class="table-responsive">        
                <table id="ledgerTbl" class="table">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th width="40%">Ledger Account Name</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Name</th>
                            <th>Project</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($journal->items as $item)
                            <tr>
                                <td>{{ @$item->account->holder }}</td>
                                <td>{{ numberFormat($item->debit) }}</td>
                                <td>{{ numberFormat($item->credit) }}</td>
                                <td>
                                    @php
                                        if ($item->customer) {
                                            echo $item->customer->company ?:  $item->customer->name;
                                        } elseif ($item->supplier) {
                                            echo $item->supplier->company ?:  $item->supplier->name;
                                        }
                                    @endphp
                                </td>
                                <td>{{ $item->project? gen4tid('PRJ-', $item->project->tid) . '-' . $item->project->name : '' }}</td>                                
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
