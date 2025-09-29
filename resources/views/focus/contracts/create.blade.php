@extends ('core.layouts.app')

@section ('title', 'Create | Contract Management')

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
                            {{ Form::open(['route' => 'biller.contracts.store', 'files'=> true]) }}
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
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());   

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

    // auto generate default schedules
    let rowId = 0;
    const scheduleRow = $('#scheduleTbl tbody tr').html();
    $('form').on('change', '#periodYr, #periodMn', function() {
        const yrs = $('#periodYr').val();
        const months = $('#periodMn').val();
        if (!yrs || !months) return;

        const n = Math.round(yrs * 12 / months);
        $('#scheduleTbl tbody tr').remove();
        Array.from({length: n}, v => v).forEach(v => {
            rowId++;
            let html = scheduleRow.replace(/-0/g, '-'+rowId);
            $('#scheduleTbl tbody').append('<tr>' + html + '</tr>');
            $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
            .datepicker('setDate', new Date());  
        });
    });
    $('#periodYr').change();
    // add schedule row
    $('#addSchedule').click(function() {
        rowId++;
        let html = scheduleRow.replace(/-0/g, '-'+rowId);
        $('#scheduleTbl tbody').append('<tr>' + html + '</tr>');
        $('.datepicker')
        .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true}) 
        $('#startdate-'+ rowId).datepicker('setDate', new Date());
        $('#enddate-'+ rowId).datepicker('setDate', new Date());
    });
    // remove schedule row
    $('#scheduleTbl').on('click', '.remove', function() {
        $(this).parents('tr').remove();
        rowId--;
    });

    let docRowId = 0;
    const docRow = $('#docTbl tbody tr').html();
    $('#addDoc').click(function() {
        docRowId++;
        let html = docRow.replace(/-0/g, '-'+docRowId);
        $('#docTbl tbody').append('<tr>' + html + '</tr>');
    });
    // remove schedule row
    $('#docTbl').on('click', '.remove_doc', function() {
        $(this).parents('tr').remove();
        docRowId--;
    });

    // on change customer or branch load equipments
    const equipRow =  $('#equipmentTbl tbody tr').html();
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