@extends ('core.layouts.app')

@section ('title', 'Add Equipment | Contract Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Contract Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.contracts.partials.contracts-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::open(['route' => 'biller.contracts.store_add_equipment', 'method' => 'POST']) }}
                                <div class="form-group row">
                                    <div class="col-6">
                                        <label for="customer">Customer</label>
                                        <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose customer" required>
                                            @isset($contract)
                                                <option value="{{ $contract->customer_id }}" selected>
                                                    {{ $contract->customer? $contract->customer->company : '' }}
                                                </option>
                                            @endisset
                                        </select>
                                    </div>      
                                    <div class="col-6">
                                        <label for="contract">Contract</label>
                                        <select name="contract_id" id="contract" class="form-control" data-placeholder="Choose Contract" required>
                                            <option value="">-- Select Contract --</option>                                        
                                        </select>
                                    </div>                               
                                </div>                               

                                <legend>Customer Equipments</legend><hr>    
                                <div class="form-group form-inline">
                                    <label for="branch">Branch</label>
                                    <div class="col-2">
                                        <select name="branch_id" id="branch" class="form-control" data-placeholder="Choose branch"></select>
                                    </div>
                                </div>                               
                                <div class="table-responsive mb-1">
                                    <table id="equipmentTbl" class="table">
                                        <thead>
                                            <tr>
                                                <th>Serial No</th>
                                                <th>Type</th>
                                                <th>Branch</th>
                                                <th>Location</th>
                                                <th>                    
                                                    Action
                                                    <div class="d-inline ml-2">
                                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- equipment row template -->
                                            <tr class="d-none">
                                                <td>#unique_id</td>
                                                <td>#make_type</td>
                                                <td>#branch</td>
                                                <td>#location</td>
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input ml-1 select">
                                                    </div>
                                                </td>
                                                <input type="hidden" name="equipment_id[]" value="#id" class="equipId" disabled>
                                            </tr>                                            
                                        </tbody>
                                    </table>
                                </div>
                                <div class="form-group row">
                                    <div class="col-11">
                                        {{ Form::submit('Add Equipment', ['class' => 'btn btn-primary float-right btn-lg']) }}
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    // form submit
    $('form').submit(function(e) {
        const equipments = $('#equipmentTbl .equipId:not(:disabled)').length;
        if (equipments < 1) {
            e.preventDefault();
            alert('Include at least one equipment!');
        }
    });    

    // select2 config
    function select2Config(url, callback, extraData) {
        return {
            allowClear: true,
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, ...extraData}),
                quietMillis: 50,
                processResults: callback
            }
        }
    }

    const customerUrl = "{{ route('biller.customers.select') }}";
    const customerCb = data => ({ results: data.map(v => ({id: v.id, text: v.name + ' - ' + v.company})) });
    $('#customer').select2(select2Config(customerUrl, customerCb));

    const branchUrl = "{{ route('biller.branches.select') }}";
    const branchCb = data => ({ results: data.map(v => ({id: v.id, text: v.name})) });
    $('#branch').select2();

    // on change customer or branch load equipments
    const equipRow =  $('#equipmentTbl tbody tr').html();
    $('form').on('change', '#customer, #branch, #contract', function() {
        if ($(this).is('#customer')) {            
            const customer_id = $(this).val();
            $('#branch').select2(select2Config(branchUrl, branchCb, {customer_id}));
            $('#contract option:not(:eq(0))').remove();
            $('#equipmentTbl tbody tr').remove();
            if (!customer_id) return;

            // load customer contracts
            $.ajax({
                url: "{{ route('biller.contracts.customer_contracts') }}",
                type: 'POST',
                data: {customer_id},
                success: data => data.forEach(v => $('#contract').append(new Option(v.title, v.id)))
            });
        } else {
            // load contract equipments
            $('#equipmentTbl tbody tr').remove();
            $.ajax({
                url: "{{ route('biller.contracts.customer_equipment')  }}",
                type: 'POST',
                data: {
                    customer_id: $('#customer').val(), 
                    branch_id: $('#branch').val(),
                    contract_id: $('#contract').val(),
                },
                success: data => data.forEach(fillTable),
            });
        }
    });
    function fillTable(obj) {
        let elements = ['#id', '#unique_id', '#make_type', '#branch', '#location'];
        let html = equipRow.replace('d-none', '');
        elements.forEach(el => {
            for (let p in obj) {
                if ('#'+p == el && p == 'branch') html = html.replace(el, obj.branch.name);
                else if ('#'+p == el) html = html.replace(el, obj[p]? obj[p] : '');
            }
        });
        $('#equipmentTbl tbody').append('<tr>' + html + '</tr>');
    }

    // on change row checkbox
    $('#equipmentTbl').on('change', '.select', function() {
        const select = $(this).is(':checked');
        const equipId = $(this).parents('tr').find('.equipId');
        if (select) equipId.attr('disabled', false);
        else equipId.attr('disabled', true);
    })
    // on change action checkbox
    $('#selectAll').change(function() {
        const selectAll = $(this).is(':checked');
        $('#equipmentTbl tbody tr').each(function() {
            if (selectAll) $(this).find('.select').prop('checked', true).change();
            else $(this).find('.select').prop('checked', false).change();
        });
    });    
</script>
@endsection