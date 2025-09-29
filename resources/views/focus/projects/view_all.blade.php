@extends ('core.layouts.app',['page'=>'class="horizontal-layout horizontal-menu content-detached-right-sidebar" data-open="click" data-menu="horizontal-menu" data-col="content-detached-right-sidebar" '])

@section ('title', 'View All Round Report' )

@section('content')
    <!-- BEGIN: Content-->
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h3 class="content-header-title">View All Round Report</h3>
            </div>
            <div class="col-6">
                <div class="media-body media-right text-right">
                    @include('focus.projects.partials.projects-header-buttons')
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ gen4tid("#", $project->tid) }}: {{ $project['name'] }}</h4>
            </div>
            <div class="card-content">
                <div class="card-body" id="pro_tabs">
                    <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab1" data-toggle="tab" href="#tab_data1" aria-controls="tab_data1" role="tab" aria-selected="true">
                                <i class="fa fa-lightbulb-o"></i> Ticket
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab2" data-toggle="tab" href="#tab_data2" aria-controls="tab_data2" role="tab" aria-selected="true">
                                <i class="ft-file-text"></i> DJC
                            </a>
                        </li> 
                        <li class="nav-item">
                            <a class="nav-link" id="tab14" data-toggle="tab" href="#tab_data14" aria-controls="tab_data14" role="tab" aria-selected="true">
                                <i class="fa fa-money"></i>Project
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab13" data-toggle="tab" href="#tab_data13" aria-controls="tab_data13" role="tab" aria-selected="true">
                                <i class="ft-file-text"></i> Quote / PI
                            </a>
                        </li> 

                        <!-- Expense Report -->
                        <li class="nav-item">
                            <a class="nav-link" id="tab3" data-toggle="tab" href="#tab_data3" aria-controls="tab_data3" role="tab" aria-selected="true">
                                <i class="ft-file-text"></i> Budget (Quote / PI)
                            </a>
                        </li> 

                        <li class="nav-item">
                            <a class="nav-link" id="tab4" data-toggle="tab" href="#tab_data4" aria-controls="tab_data4" role="tab" aria-selected="true">
                                <i class="fa fa-flag-checkered"></i> Expenses
                            </a>
                        </li>

                       <li class="nav-item">
                           <a class="nav-link" id="tab5" data-toggle="tab" href="#tab_data5" aria-controls="tab_data5" role="tab" aria-selected="true">
                               <i class="icon-directions"></i> Milestones
                           </a>
                       </li>

                       <li class="nav-item">
                           <a class="nav-link" id="tab6" data-toggle="tab" href="#tab_data6" aria-controls="tab_data6" role="tab" aria-selected="true">
                               <i class="ft-users"></i> Tasks
                           </a>
                       </li>

{{--                        @if($project->creator->id==auth()->user()->id)--}}
{{--                            <li class="nav-item">--}}
{{--                                <a class="nav-link" id="tab5" data-toggle="tab" href="#tab_data4" aria-controls="tab_data4" role="tab" aria-selected="true">--}}
{{--                                    <i class="fa fa-list-ol"></i> Project Log--}}
{{--                                </a>--}}
{{--                            </li> --}}
{{--                        @endif--}}

                       <li class="nav-item">
                           <a class="nav-link" id="tab7" data-toggle="tab" href="#tab_data7" aria-controls="tab_data7" role="tab" aria-selected="true">
                               <i class="icon-note"></i>Notes
                            </a>
                        </li> 

                       <li class="nav-item">
                           <a class="nav-link" id="tab8" data-toggle="tab" href="#tab_data8" aria-controls="tab_data8" role="tab" aria-selected="true">
                               <i class="fa fa-paperclip"></i> {{trans('general.files')}}
                           </a>
                       </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" id="tab9" data-toggle="tab" href="#tab_data9" aria-controls="tab_data9" role="tab" aria-selected="true">
                                <i class="ft-file-text"></i> {{trans('invoices.invoices')}}
                            </a>
                        </li> 

                        <!-- Gross Profit Report -->
                        <li class="nav-item">
                            <a class="nav-link" id="tab10" data-toggle="tab" href="#tab_data10" aria-controls="tab_data10" role="tab" aria-selected="true">
                                <i class="fa fa-money"></i>Gross Profit
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" id="tab12" data-toggle="tab" href="#tab_data12" aria-controls="tab_data12" role="tab" aria-selected="true">
                                <i class="fa fa-money"></i>RJC
                            </a>
                        </li>
                    </ul>

                    {{-- tab content --}}
                    <div class="tab-content px-1 pt-1">
                        {{-- tabs 1 to 12 --}}
                        @include('focus.projects.report_tabs.ticket')
                        @include('focus.projects.report_tabs.djcs')
                        @include('focus.projects.report_tabs.quote_pi')
                        @include('focus.projects.report_tabs.budgets')
                        @include('focus.projects.report_tabs.expenses')
                        @include('focus.projects.report_tabs.milestones')
                        @include('focus.projects.report_tabs.tasks')
                        @include('focus.projects.report_tabs.notes')
                        @include('focus.projects.report_tabs.files')
                        @include('focus.projects.report_tabs.invoices')
                        @include('focus.projects.report_tabs.gross_profit')
                        @include('focus.projects.report_tabs.rjcs')
                        @include('focus.projects.report_tabs.project_summary')
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    <input type="hidden" id="loader_url" value="{{route('biller.tasks.load')}}">

   
@endsection

@section('after-styles')
    {{ Html::style('core/app-assets/css-'.visual().'/pages/project.css') }}
    {!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection

@section('after-scripts')
{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}
    <script>
        const config = {
        ajax: {
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}
        },
        date: {autoHide: true, format: '{{config('core.user_date_format')}}'},
    };
    // ajax header set up
    $.ajaxSetup(config.ajax);
        expenses();
        // fetch expenses
    ['accountLedger', 'supplier', 'product_name'].forEach(v => $('#'+v).select2({allowClear: true}));
    ['expCategory', 'accountLedger', 'supplier', 'product_name'].forEach(v => $('#'+v).change(() =>  expenses(render=true)));
    function expenses(render=false) {
        if (!render) {
            if ($('#expItems tbody tr').length) return;
        } else $('#expItems').DataTable().destroy();

        $('#expItems').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.projects.get_expense') }}",
                type: 'POST',
                data: {
                    project_id: "{{ $project->id }}",
                    exp_category: $('#expCategory').val(),
                    ledger_id: $('#accountLedger').val(),
                    supplier_id: $('#supplier').val(),
                    product_name: $('#product_name').val(),
                },
                dataSrc: ({data}) => {
                    if (data.length) {
                        const groupTotals = data[0]['group_totals'];
                        $('#expTotals tbody td').each(function(i) {
                            let row = $(this);
                            switch (i) {
                                case 0: row.text(accounting.formatNumber(groupTotals['inventory_stock'])); break;
                                case 1: row.text(accounting.formatNumber(groupTotals['labour_service'])); break;
                                case 2: row.text(accounting.formatNumber(groupTotals['dir_purchase_stock'])); break;
                                case 3: row.text(accounting.formatNumber(groupTotals['dir_purchase_service'])); break;
                                case 4: row.text(accounting.formatNumber(groupTotals['purchase_order_stock'])); break;
                                case 5: row.text(accounting.formatNumber(groupTotals['grand_total'])); break;
                            }
                        });
                    }
                    return data;
                },
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                ...['exp_category', 'milestone', 'supplier', 'product_name','date', 'uom', 'qty', 'rate', 'amount']
                .map(v => ({data: v, name: v})),
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
    </script>

@endsection