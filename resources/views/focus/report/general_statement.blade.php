@extends ('core.layouts.app')
@section ('title', $lang['title'].' | ' . trans('features.reports'))

@section('content')
<div class="content-wrapper">
    <div class="content-body">
        <div class="card">
            <div class="card-header">
                <h5>{{ $lang['title'] }}</h5>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                        <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                        <li><a data-action="close"><i class="ft-x"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="card-content">
                <div id="notify" class="alert alert-success" style="display:none;">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>
                    <div class="message"></div>
                </div>
                <div class="card-body">
                    @include('focus.report.'.$lang['module'])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        datepicker: {
            autoHide: true,
            format: '{{config('core.user_date_format')}}'
        },
        ajax: {
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}
        },
    };

    $('[data-toggle="datepicker"]').datepicker(config.datepicker);
    $('.from_date').datepicker('setDate', '{{dateFormat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d')))))}}');
    $('.from_date').datepicker(config.datepicker);
    $('.to_date').datepicker('setDate', 'today');
    $('.to_date').datepicker(config.datepicker);
    $('.record_month').datepicker({
        autoHide: true,
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        format: 'MM-yyyy',
        onClose: function(dateText, inst) { 
            $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
        }
    });

    $.ajaxSetup(config.ajax);
    $("#person").select2({
        allowClear: true,
        tags: [],
        ajax: {
            @if($lang['module']=='customer_statement' OR $lang['module']=='product_customer_statement')
            url: '{{route('biller.customers.select')}}',
            @elseif($lang['module']=='supplier_statement' OR $lang['module']=='product_supplier_statement')
            url: '{{route('biller.suppliers.select')}}',
            @endif
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: (person) => ({person: person}),
            processResults: (data) => {
                return {results: data.map(v => ({text: v.name, id: v.id}))}
            },
        }
    });

    $("#products_l").select2({allowClear: true});

    $("#wfrom").on('change', function () {
        $("#products_l").select2({
            allowClear: true,
            tags: [],
            ajax: {
                url: "{{ route('biller.products.product_search', ['label']) }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: (product) => ({keyword: product, wid: $('#wfrom').val()}),
                processResults: (data) => {
                    return {results: data.map(v => ({text: v.name, id: v.id}))}
                },
            }
        });
    });

    $("#product").select2({
        allowClear: true,
        ajax: {
            url: "{{ route('biller.products.quote_product_search') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({keyword: term}),
            processResults: (data) => {
                return {results: data.map(v => ({text: `${v.name} (${v.code}) ${v.warehouse? '(' + v.warehouse.title + ')' : ''}`, id: v.id}))}
            },
        }
    });
</script>
@endsection