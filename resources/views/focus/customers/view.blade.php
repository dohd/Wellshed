@extends ('core.layouts.app')

@section ('title', 'Customer Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Customer Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.customers.partials.customers-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-detached content-right">
        <div class="content-body">
            <section class="row all-contacts">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                @if (access()->allowMultiple(['edit-client', 'delete-client']))
                                    <div class="btn-group float-right">
                                        @permission('edit-client')
                                            <a href="{{ route('biller.customers.edit', $customer) }}" class="btn btn-blue btn-outline-accent-5 btn-sm">
                                                <i class="fa fa-pencil"></i> {{trans('buttons.general.crud.edit')}}
                                            </a>&nbsp;
                                        @endauth
                                        @permission('delete-client')
                                            <button type="button" class="btn btn-danger btn-outline-accent-5 btn-sm" id="delCustomer">
                                                {{ Form::open(['route' => ['biller.customers.destroy', $customer], 'method' => 'DELETE']) }}{{ Form::close() }}
                                                <i class="fa fa-trash"></i> {{ trans('buttons.general.crud.delete') }}
                                            </button>
                                        @endauth
                                    </div>
                                @endif
                                <div class="card-body">
                                    @include('focus.customers.partials.tabs')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('focus.customers.partials.sidebar')
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    config = {
        ajax: {
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        dataTable: {
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
        }
    };

    const View = {
        startDate: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            View.drawCustomerDataTable();

            $('.start_date').change(View.changeStartDate);
            $('.search').click(View.searchClick);
            $('.refresh').click(View.refreshClick);
            $('#delCustomer').click(View.deleteCustomer);
        },

        deleteCustomer() {
            const form = $(this).children('form');
            swal({
                title: 'Are You  Sure?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
                showCancelButton: true,
            }, () => form.submit());
        },        

        drawCustomerDataTable() {
            $('#customerTbl').DataTable({
                ...config.dataTable,
                ajax: {
                    url: '{{ route("biller.customers.get") }}',
                    type: 'post',
                    data: { customer_id: "{{ $customer->id }}" },
                },
                columns: [{ data: 'company', name: 'company' }],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'lfrtip', // l adds the length menu
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]], // Add rows selection and "All" option
            });
        },        
    };

    $(View.init);
</script>
@endsection