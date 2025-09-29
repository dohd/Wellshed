@extends('core.layouts.app')

@section('title', 'Edit | Schedule Management')

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
                            {{ Form::model($taskschedule, ['route' => ['biller.taskschedules.update', $taskschedule], 'method' => 'PATCH']) }}
                                <div class="form-group row">
                                    <div class="col-3">
                                        <label for="title">Schedule Title</label>
                                        {{ Form::text('title', null, ['class' => 'form-control', 'required']) }}
                                    </div>
                                    <div class="col-2">
                                        <label for="start_date">Start Date</label>
                                        {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'id' => 'start_date']) }}
                                    </div>
                                    <div class="col-2">
                                        <label for="end_date">End Date</label>
                                        {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'id' => 'end_date']) }}
                                    </div>   
                                    <div class="col-2">
                                        <label for="start_date">Actual Start Date</label>
                                        {{ Form::text('actual_startdate', null, ['class' => 'form-control datepicker', 'id' => 'actual_startdate']) }}
                                    </div>
                                    <div class="col-2">
                                        <label for="end_date">Actual End Date</label>
                                        {{ Form::text('actual_enddate', null, ['class' => 'form-control datepicker', 'id' => 'actual_enddate']) }}
                                    </div>                                   
                                </div>
                                <legend>Equipments</legend>
                                <div class="table-reponsive mb-2">
                                    <table id="equipmentTbl" class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Equipment No.</th>
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
                                            @foreach ($taskschedule->equipments as $i => $row)                                            
                                                <tr>
                                                    <td>{{ $i+1 }}</td>
                                                    <td>{{ gen4tid('Eq-', $row->tid) }}</td>
                                                    <td>{{ $row->equip_serial }}</td>
                                                    <td>{{ $row->make_type }}</td>
                                                    <td>{{ $row->branch->name }}</td>
                                                    <td>{{ $row->location }}</td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input ml-1 select">
                                                        </div>
                                                    </td>
                                                    <input type="hidden" class="equipId" name="id[]" value="{{ $row->id }}" disabled>
                                                </tr>                                                        
                                            @endforeach                                                    
                                        </tbody>
                                    </table>                                    
                                </div>
                                <div class="form-group row">
                                    {{ Form::hidden('equipment_ids', null, ['id' => 'equipment_ids']) }}
                                    <div class="col-11">
                                        {{ Form::submit('Update', ['class' => 'btn btn-primary float-right btn-lg']) }}
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

    const schedule = @json($taskschedule);
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());
    $('#start_date').datepicker('setDate', new Date(schedule.start_date));
    $('#end_date').datepicker('setDate', new Date(schedule.end_date));  
    $('#actual_startdate').datepicker('setDate', new Date(schedule.actual_startdate));
    $('#actual_enddate').datepicker('setDate', new Date(schedule.actual_enddate));       
    
    // on change row checkbox
    $('#equipmentTbl').on('change', '.select', function() {
        const equipId = $(this).parents('tr').find('.equipId');
        if ($(this).is(':checked')) equipId.attr('disabled', false);
        else equipId.attr('disabled', true);
    });

    // on change action checkbox
    $('#selectAll').change(function() {
        const selectAll = $(this).is(':checked');
        $('#equipmentTbl tbody tr').each(function() {
            if (selectAll) {
                $(this).find('.select').prop('checked', true);
                $(this).find('.equipId').prop('disabled', false);
            } else {
                $(this).find('.select').prop('checked', false);
                $(this).find('.equipId').prop('disabled', true);
            }  
        });
    });
    $('#selectAll').prop('checked', true).change();
</script>
@endsection