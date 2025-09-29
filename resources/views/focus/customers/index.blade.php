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
    
    <div class="content-detached content-right d-none d-sm-block">
        <div class="content-body">
            <section class="row all-contacts">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <form action="{{route('biller.customers.send_statement')}}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-lg btn-success d-none">Send Statements</a>
                            </form>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Customer Info</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Statement on Account</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Invoice</a>
                                        </li> 
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">Statement on Invoice</a>
                                        </li>                                       
                                    </ul>
                                    <div class="tab-content px-1 pt-1">
                                        <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                            <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                                                @php
                                                    $labels = [
                                                        'Name', 'Phone', 'Email', 'Address', 'Company', 'City', 'Region', 'Country', 'PostBox',
                                                        'Tax ID' => 'taxid',  
                                                        'Contact Person Info' => 'contact_person_info'
                                                    ];
                                                @endphp
                                                <tbody> 
                                                    @foreach ($labels as $key => $val)
                                                        <tr>
                                                            <th>{{ is_numeric($key) ? $val : $key }}</th>
                                                            <td></td>
                                                        </tr>
                                                    @endforeach                        
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
                                            <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>                                            
                                                        @foreach (['Date', 'Type', 'Note', 'Credit', 'Debit'] as $val)
                                                            <th>{{ $val }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                        <div class="tab-pane" id="active3" aria-labelledby="link-tab3" role="tabpanel">
                                            <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>                                                    
                                                        @foreach (['Date', 'Type', 'Note', 'Amount', 'Paid', 'Balance'] as $val)
                                                            <th>{{ $val }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>                                               
                                            </table>                                            
                                        </div>
                                        <div class="tab-pane" id="active4" aria-labelledby="link-tab4" role="tabpanel">
                                          <!-- aging tab -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <div class="sidebar-detached sidebar-left">
        <div class="sidebar">
            <div class="bug-list-sidebar-content">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="customerTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ trans('customers.name') }}</th>
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
                {
                    data: 'company',
                    name: 'company'
                },
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