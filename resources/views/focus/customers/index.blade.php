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
    
    <div class="content-body">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="customerTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>CUSTOMER NAME</th>
                                <th>COMPANY</th>
                                <th>EMAIL</th>
                                <th>PHONE NO.</th>
                                <th>RECEIVABLES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="100%" class="text-center text-success font-large-1">
                                    <i class="fa fa-spinner spinner"></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>                            
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    
    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        const dataTable = $('#customerTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.customers.get") }}',
                type: 'post'
            },
            columns: [
                {data: 'DT_Row_Index',name: 'id'},
                ...['name', 'company', 'email', 'phone', 'balance'].map(v => ({name: v, data: v}))
            ],
            ordering: false,
            searchDelay: 500,
            // dom: 'frt',
            dom: 'Blfrtip',
            buttons: [],
            lengthMenu: [
                [15, 25, 50, 100, 200, -1],
                [15, 25, 50, 100, 200, "All"]
            ],
        });
    }
</script>
@endsection