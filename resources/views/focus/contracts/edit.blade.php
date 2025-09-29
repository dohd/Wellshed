@extends ('core.layouts.app')

@section ('title', 'Edit | Contract Management')

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
                            {{ Form::model($contract, ['route' => ['biller.contracts.update', $contract], 'method' => 'PATCH', 'files' => true]) }}
                                @include('focus.contracts.form')
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
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    $('.filter-block').addClass('d-none');

    const contract = @json($contract);
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});
    $('#start_date').datepicker('setDate', new Date(contract.start_date));
    $('#end_date').datepicker('setDate', new Date(contract.end_date));   

    
    $('#amount').focusout(function () {
        const value = accounting.unformat($(this).val());
        $(this).val(accounting.formatNumber(value));
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
    const branchCb = data => ({ results: data.filter(v => v.name != 'All Branches').map(v => ({id: v.id, text: v.name})) });
    $('#branch').select2();    
    
    // add schedule row
    const scheduleRow = $('#scheduleTbl tbody tr:first').html();
    $('#scheduleTbl tbody tr:first').remove();
    let rowId = $('#addSchedule tbody').length;
    $('#addSchedule').click(function() {
        rowId++;
        let html = scheduleRow.replace(/-0/g, '-'+rowId);
        $('#scheduleTbl tbody').append('<tr>' + html + '</tr>');
        $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});
        $('#startdate-'+ rowId).datepicker('setDate', new Date());
        $('#enddate-'+ rowId).datepicker('setDate', new Date());
    });
    // remove schedule row
    $('#scheduleTbl').on('click', '.remove', function() {
        $(this).parents('tr').remove();
    });
    // add doc row
    const docRow = $('#docTbl tbody tr:first').html();
    $('#docTbl tbody tr:first').remove();
    let docRowId = $('#addDoc tbody').length;
    $('#addDoc').click(function() {
        docRowId++;
        let html = docRow.replace(/-0/g, '-'+docRowId);
        $('#docTbl tbody').append('<tr>' + html + '</tr>');
        $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});
        $('#startdate-'+ docRowId).datepicker('setDate', new Date());
        $('#enddate-'+ docRowId).datepicker('setDate', new Date());
    });
    // remove doc row
    $('#docTbl').on('click', '.remove_doc', function() {
        $(this).parents('tr').remove();
    });

    // on change customer or branch load equipments
    const equipRow =  $('#equipmentTbl tbody tr').html();
    $('#equipmentTbl tbody tr:first').remove();
    $('form').on('change', '#customer, #branch', function() {
        if ($(this).is('#customer')) {
            const customer_id = $(this).val();
            $('#branch').select2(select2Config(branchUrl, branchCb, {customer_id}));
            $.ajax({
                url: "{{ route('biller.contracts.customer_equipment')  }}",
                type: 'POST',
                data: {customer_id},
                success: data => {
                    $('#equipmentTbl tbody tr').remove();
                    data.forEach(fillTable);
                }
            });
        } else {
            const customer_id = $('#customer').val();
            const branch_id = $(this).val();
            $.ajax({
                url: "{{ route('biller.contracts.customer_equipment')  }}?branch_id=" + branch_id,
                type: 'POST',
                data: {customer_id, branch_id},
                success: data => {
                    $('#equipmentTbl tbody tr').remove();
                    data.forEach(fillTable);
                }
            });
        }
        $('#selectAll').prop('checked', false).change();
    });
    function fillTable(obj) {
        let elements = ['#id', '#equip_serial', '#make_type', '#branch', '#location'];
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
        const equipId = $(this).parents('tr').find('.equipId');
        const contEquipId = $(this).parents('tr').find('.contEquipId');
        if ($(this).is(':checked')) {
            equipId.attr('disabled', false);
            contEquipId.attr('disabled', false);
        } else {
            equipId.attr('disabled', true);
            contEquipId.attr('disabled', true);
        }        
    })
    // on change action checkbox
    $('#selectAll').change(function() {
        const selectAll = $(this).is(':checked');
        $('#equipmentTbl tbody tr').each(function() {
            if (selectAll) $(this).find('.select').prop('checked', true).change();
            else $(this).find('.select').prop('checked', false).change();
        });
    });
    $('#selectAll').prop('checked', true).change();
</script>
@endsection