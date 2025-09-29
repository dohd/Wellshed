@extends('core.layouts.app')

@section('title', 'Load Machine | Schedule Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Schedule Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                @include('focus.taskschedules.partials.taskschedule-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::open(['route' => 'biller.taskschedules.store']) }}
                                @include('focus.taskschedules.form')
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
    $('form').submit(function() {
        const equipment_ids = [];
        $('#equipmentTbl tbody tr').each(function() {
            if ($(this).find('.select').prop('checked')) {
                equipment_ids.push($(this).find('.equipId').val())
            }
        });
        ['.equipId', '.rate'].forEach(v => $(v).remove());
        $('#equipment_ids').val(equipment_ids.join(','));
    });


    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    // on taskschedule change
    $('#schedule').select2({allowClear: true}).val('').change();
    $('#schedule').change(function () {
        const opt = $(this).find(':selected');
        const startDate = $(this).val()? new Date(opt.attr('actual_start')) : new Date();
        const endDate = $(this).val()? new Date(opt.attr('actual_end')) : new Date();
        $('#actual_startdate').datepicker('setDate', startDate);
        $('#actual_enddate').datepicker('setDate', endDate);
    });

    // on contract select
    const equipRow =  $('#equipmentTbl tbody tr').html();
    $('#contract').select2({allowClear: true});
    $('#contract').change(function() {
        const contract_id = $(this).val();
        $('#equipmentTbl tbody tr').remove();
        $('#schedule option:not(:first)').remove();

        // load task schedule options
        $.ajax({
            url: "{{ route('biller.contracts.task_schedules')  }}",
            type: 'POST',
            data: {contract_id},
            success: data => {
                data.forEach(v => {
                    const actualStartDate = v.actual_startdate && v.actual_startdate.substring(0,3) == v.start_date.substring(0,3) ? v.actual_startdate : v.start_date;
                    const actualEndDate = v.actual_enddate && v.actual_enddate.substring(0,3) == v.end_date.substring(0,3) ? v.actual_enddate : v.end_date;
                    const option = `<option value="${v.id}" actual_start="${actualStartDate}" actual_end="${actualEndDate}">${v.title}</option>`;
                    $('#schedule').append(option);
                });               
            }
        });

        // load equipments
        $.ajax({
            url: "{{ route('biller.contracts.contract_equipment') }}",
            type: 'POST',
            data: {contract_id, is_schedule: 1},
            success: data => {
                data.forEach(equipment => {
                    let html = equipRow.replace('d-none', '');
                    let elementIds = ['#id', '#tid', '#equip_serial', '#make_type', '#branch', '#location', '#service_rate'];
                    elementIds.forEach(id => {
                        for (let prop in equipment) {
                            if ('#'+prop == id && prop == 'branch') {
                                html = html.replace(id, equipment.branch.name);
                            } else if ('#'+prop == id && prop == 'service_rate') {
                                const serviceRate = parseFloat(equipment.service_rate);
                                html = html.replace(id, accounting.formatNumber(serviceRate))
                                .replace(id, equipment.service_rate);
                            } else if ('#'+prop == id) {
                                html = html.replace(id, equipment[prop]? equipment[prop] : '');
                            }                
                        }
                    });
                    $('#equipmentTbl tbody').append('<tr>' + html + '</tr>');
                });
            },
        })
    });

    // on change row checkbox
    $('#equipmentTbl').on('change', '.select', function() {
        const equipId = $(this).parents('tr').find('.equipId');
        const rate = $(this).parents('tr').find('.rate');
        if ($(this).is(':checked')) {
            equipId.attr('disabled', false);
            rate.attr('disabled', false);
        } else {
            equipId.attr('disabled', true);
            rate.attr('disabled', true);
        }  
        calcTotal();
    })

    // on change action checkbox
    $('#selectAll').change(function() {
        const selectAll = $(this).is(':checked');
        $('#equipmentTbl tbody tr').each(function() {
            if (selectAll) {
                $(this).find('.select').prop('checked', true);
                $(this).find('.equipId').prop('disabled', false);
                $(this).find('.rate').prop('disabled', false);
            } else {
                $(this).find('.select').prop('checked', false);
                $(this).find('.equipId').prop('disabled', true);
                $(this).find('.rate').prop('disabled', true);
            }  
        });
        calcTotal();
    });
    
    // compute total rate
    function calcTotal() {
        let totalRate = 0;
        $('#equipmentTbl tbody tr').each(function() {
            const rate = accounting.unformat($(this).find('.rate:not(:disabled)').val());
            totalRate += rate;
        });
        $('#totalRate').val(accounting.formatNumber(totalRate));
    }
</script>
@endsection