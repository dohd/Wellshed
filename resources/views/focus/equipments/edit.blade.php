@extends ('core.layouts.app')

@section ('title', 'Edit | Equipment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Edit Equipment</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.equipments.partials.equipments-header-buttons')
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
                            {{ Form::model($equipment, ['route' => ['biller.equipments.update', $equipment], 'method' => 'PATCH', 'id' => 'edit-equipment']) }}
                                @include("focus.equipments.form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.equipments.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                    {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
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
    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    }

    $.ajaxSetup(config.ajax);
    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

    const equipment = @json($equipment);
    if (equipment.install_date) {
        $('.datepicker').datepicker('setDate', new Date(equipment.install_date));
    }
    if (equipment.end_of_warranty){
        $('#end_of_warranty').datepicker('setDate', new Date(equipment.end_of_warranty));
    }

    // fetch customers
    $("#person").select2({
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({search: term}),
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name+' - '+item.company,
                            id: item.id
                        }
                    })
                };
            },
        }
    });


    $("#person").on('change', function () {
        $("#branch").val('').change();
        var tips = $('#person').val();
        // fetch branches
        $("#branch").select2({
            ajax: {
                url: "{{ route('biller.branches.select') }}?customer_id=" + tips,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                params: {'cat_id': tips},
                data: ({term}) => ({search: term, customer_id: $(this).val()}), 
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
            }
        });
    });
</script>
@endsection