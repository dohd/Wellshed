<div class='form-group row'>
    <div class='col-md-2'>
        <div><label for="tid">#Serial</label></div>
        {{ Form::text('tid', @$tid ?: @$journal->tid, ['class' => 'form-control', 'readonly']) }}
    </div>
    <div class='col-md-2'>
        <div><label for="date">Date</label></div>
        <input type="text" name="date" class="form-control datepicker">
    </div>
    <div class='col-md-8'>
        <div><label for="note">Note</label></div>
        {{ Form::text('note', null, ['class' => 'form-control', 'required']) }}
    </div>
</div>
<div class="table-responsive">        
    <table id="ledgerTbl" class="table text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="20%">Account</th>
                <th>Debit</th>
                <th>Credit</th>
                <th width="20%">Name</th>
                <th width="20%">Project</th>
                <th width="10%">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="20%">
                    <select name="account_id[]" id="account-0" class="form-control account" data-placeholder="Search Account">
                        <option value=""></option>
                    </select>
                </td>
                <td><input type="text" class="form-control debit" name="debit[]" placeholder="0.00" id="debit-0" autocomplete="off"></td>
                <td><input type="text" class="form-control credit" name="credit[]" placeholder="0.00" id="credit-0" autocomplete="off"></td>
                <td width="20%">
                    <select id="name-0" class="form-control name" data-placeholder="Customer / Vendor" disabled>
                        <option value=""></option>
                    </select>
                    <input type="hidden" name="customer_id[]" class="customer-id">
                    <input type="hidden" name="supplier_id[]" class="supplier-id">
                </td>
                <td width="20%">
                    <select name="project_id[]" id="project-0" class="form-control project" data-placeholder="Search Project">
                        <option value=""></option>
                    </select>
                </td>
                <td><button type="button" class="btn btn-danger btn-sm d-none remove ml-2 mt-1"><i class="fa fa-trash fa-lg"></i> Remove</button></td>
            </tr>
            @foreach (@$journal->items ?: [] as $i => $item)
                <tr>
                    <td width="20%">
                        <select name="account_id[]" id="account-{{$i}}" class="form-control account" data-placeholder="Search Account">
                            <option value=""></option>
                            @isset($item->account)
                                <option value="{{$item->account_id}}" selected>{{ $item->account->number }} - {{ $item->account->holder }}</option>
                            @endisset
                        </select>
                    </td>
                    
                    @if ($item->debit > 0)
                        <td><input type="text" class="form-control debit" name="debit[]" value="{{ numberFormat($item->debit) }}" placeholder="0.00" id="debit-{{$i}}" autocomplete="off"></td>
                        <td><input type="text" class="form-control credit" name="credit[]" placeholder="0.00" id="credit-{{$i}}" readonly autocomplete="off"></td>
                    @endif

                    @if ($item->credit > 0)
                        <td><input type="text" class="form-control debit" name="debit[]" placeholder="0.00" id="debit-{{$i}}" readonly autocomplete="off"></td>
                        <td><input type="text" class="form-control credit" name="credit[]" value="{{ numberFormat($item->credit) }}" placeholder="0.00" id="credit-{{$i}}" autocomplete="off"></td>
                    @endif
                    
                    <td width="20%">
                        @if ($item->supplier)
                            <select id="name-{{$i}}" class="form-control name" data-placeholder="Customer / Vendor" disabled>
                                <option value=""></option>
                                <option value="{{$item->supplier_id}}" selected>{{ $item->supplier->company }} - {{ $item->supplier->name }}</option>
                            </select>
                            <input type="hidden" name="customer_id[]" class="customer-id">
                            <input type="hidden" name="supplier_id[]" class="supplier-id" value="{{$item->supplier_id}}">
                        @elseif ($item->customer) 
                            <select id="name-{{$i}}" class="form-control name" data-placeholder="Customer / Vendor" disabled>
                                <option value=""></option>
                                <option value="{{$item->customer_id}}" selected>{{ $item->customer->company }} - {{ $item->customer->name }}</option>
                            </select>
                            <input type="hidden" name="customer_id[]" class="customer-id" value="{{$item->customer_id}}">
                            <input type="hidden" name="supplier_id[]" class="supplier-id">
                        @else
                            <select id="name-{{$i}}" class="form-control name" data-placeholder="Customer / Vendor" disabled>
                                <option value=""></option>
                            </select>
                            <input type="hidden" name="customer_id[]" class="customer-id">
                            <input type="hidden" name="supplier_id[]" class="supplier-id">
                        @endif
                    </td>

                    <td width="20%">
                        <select name="project_id[]" id="project-{{$i}}" class="form-control project" data-placeholder="Search Project">
                            <option value=""></option>
                            @isset($item->project)
                                <option value="{{$item->project_id}}" selected>{{ gen4tid('PRJ-', $item->project->tid) }} - {{ $item->project->name }}</option>
                            @endisset
                        </select>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm ml-2 mt-1 remove {{!$i? 'd-none' : ''}}"><i class="fa fa-trash fa-lg"></i> Remove</button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="form-group row">
    <div class="col-2 ml-2">
        <button type="button" class="btn btn-success btn-sm" id="add-row"><i class="fa fa-plus-square"></i>  Add Row</button>
    </div>
</div>

<div class="row">
    <div class="col-md-2 ml-auto">
        <label for="debit_total" class="mb-0">Debit Total:</label>
        <input type="text" class="form-control" name="debit_ttl" id="debitTtl" readonly autocomplete="off">
    </div>
</div>
<div class="row mb-2">
    <div class="col-md-2 ml-auto">
        <label for="debit_total" class="mb-0">Credit Total:</label>
        <input type="text" class="form-control" name="credit_ttl"  id="creditTtl" readonly autocomplete="off">
    </div>
</div>

<input type="hidden" id="select-customer" autocomplete="off">
<input type="hidden" id="select-supplier" autocomplete="off">

@section("after-scripts")
@include('focus.journals.form_js')
@endsection