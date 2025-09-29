@extends ('core.layouts.app',['page'=>'class="horizontal-layout horizontal-menu content-detached-right-sidebar" data-open="click" data-menu="horizontal-menu" data-col="content-detached-right-sidebar" '])

@section ('title', 'View | ' . trans('labels.backend.projects.management') )

@section('content')
    <!-- BEGIN: Content-->
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h3 class="content-header-title">{{trans('projects.project_summary')}}</h3>
            </div>
            <div class="col-6">
                <div class="media-body media-right text-right">
                    @include('focus.projects.partials.projects-header-buttons')
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4><b>{{ @$project->customer->company }} - {{ @$project->branch->name }}</b></h4>
                <h4 class="card-title">{{ gen4tid("#", $project->tid) }}: {{ $project['name'] }}</h4>
            </div>
            <div class="card-content">
                <div class="card-body" id="pro_tabs">
                    <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab1" data-toggle="tab" href="#tab_data1" aria-controls="tab_data1" role="tab" aria-selected="true">
                                <i class="fa fa-lightbulb-o"></i> {{trans('projects.project_summary')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab13" data-toggle="tab" href="#tab_data13" aria-controls="tab_data7" role="tab" aria-selected="true">
                                <i class="ft-file-text"></i> BoQ
                            </a>
                        </li>
                        @permission('manage-project_quote')
                        <li class="nav-item">
                            <a class="nav-link" id="tab7" data-toggle="tab" href="#tab_data7" aria-controls="tab_data7" role="tab" aria-selected="true">
                                <i class="ft-file-text"></i> Quote / PI 
                            </a>
                        </li>
                        @endauth
                         
                        @permission('manage-project_budget')
                        <li class="nav-item">
                            <a class="nav-link" id="tab8" data-toggle="tab" href="#tab_data8" aria-controls="tab_data8" role="tab" aria-selected="true">
                                <i class="ft-file-text"></i> Budget (MTO / Expns)
                            </a>
                        </li> 
                        @endauth


                        <li class="nav-item">
                            <a class="nav-link" id="tab2" data-toggle="tab" href="#tab_data2" aria-controls="tab_data2" role="tab" aria-selected="true">
                                <i class="fa fa-flag-checkered"></i> Budget Lines / Milestones
                            </a>
                        </li>

                       <li class="nav-item">
                           <a class="nav-link" id="tab3" data-toggle="tab" href="#tab_data3" aria-controls="tab_data3" role="tab" aria-selected="true">
                               <i class="icon-directions"></i> {{trans('tasks.tasks')}}
                           </a>
                       </li>

{{--                        <li class="nav-item">--}}
{{--                            <a class="nav-link" id="tab11" data-toggle="tab" href="#tab_data11" aria-controls="tab_data11" role="tab" aria-selected="true">--}}
{{--                                <i class="ft-users"></i> {{trans('projects.users')}}--}}
{{--                            </a>--}}
{{--                        </li>--}}

{{--                        @if($project->creator->id==auth()->user()->id)--}}
{{--                            <li class="nav-item">--}}
{{--                                <a class="nav-link" id="tab4" data-toggle="tab" href="#tab_data4" aria-controls="tab_data4" role="tab" aria-selected="true">--}}
{{--                                    <i class="fa fa-list-ol"></i> Project Log--}}
{{--                                </a>--}}
{{--                            </li> --}}
{{--                        @endif--}}

                       <li class="nav-item">
                           <a class="nav-link" id="tab6" data-toggle="tab" href="#tab_data6" aria-controls="tab_data6" role="tab" aria-selected="true">
                               <i class="icon-note"></i>Notes
                            </a>
                        </li> 

                       <li class="nav-item">
                           <a class="nav-link" id="tab5" data-toggle="tab" href="#tab_data5" aria-controls="tab_data5" role="tab" aria-selected="true">
                               <i class="fa fa-paperclip"></i> {{trans('general.files')}}
                           </a>
                       </li>

                       <!-- Expense Report -->
                       @permission('manage-project_expenses')
                       <li class="nav-item">
                           <a class="nav-link" id="tab9" data-toggle="tab" href="#tab_data9" aria-controls="tab_data9" role="tab" aria-selected="true">
                               <i class="ft-file-text"></i> Expenses 
                           </a>
                       </li> 
                       @endauth
                        
                       <li class="nav-item">
                           <a class="nav-link" id="tab14" data-toggle="tab" href="#tab_data14" aria-controls="tab_data14" role="tab" aria-selected="true">
                               <i class="fa fa-paperclip"></i> BoQ Valuations
                           </a>
                       </li>

                       @permission('manage-project_invoices')
                        <li class="nav-item">
                            <a class="nav-link" id="tab10" data-toggle="tab" href="#tab_data10" aria-controls="tab_data10" role="tab" aria-selected="true">
                                <i class="ft-file-text"></i> {{trans('invoices.invoices')}}
                            </a>
                        </li> 
                        @endauth

                        @permission('manage-project_gross_profit_tab')
                        <!-- Gross Profit Report -->
                        <li class="nav-item">
                            <a class="nav-link" id="tab12" data-toggle="tab" href="#tab_data12" aria-controls="tab_data12" role="tab" aria-selected="true">
                                <i class="fa fa-money"></i>Gross Profit
                            </a>
                        </li>
                        @endauth
                    </ul>

                    {{-- tab content --}}
                    <div class="tab-content px-1 pt-1">
                        {{-- tabs 1 to 12 --}}
                        @include('focus.projects.tabs.summary')
                        @include('focus.projects.tabs.boq')
                        @include('focus.projects.tabs.milestone')
                        @include('focus.projects.tabs.task')                        
                        @include('focus.projects.tabs.activity')
                        @include('focus.projects.tabs.file')                        
                        @include('focus.projects.tabs.note')
                        @include('focus.projects.tabs.attached_quote')
                        @include('focus.projects.tabs.quote_budget')
                        @include('focus.projects.tabs.boq_valuation')
                        @include('focus.projects.tabs.expense')
                        @include('focus.projects.tabs.income')
                        @include('focus.projects.tabs.user')
                        @include('focus.projects.tabs.gross_profit')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    <input type="hidden" id="loader_url" value="{{route('biller.tasks.load')}}">

    @include('focus.projects.modal.task_view')
    @include('focus.projects.modal.milestone_new')
    @include('focus.projects.modal.quote_new')
    @include('focus.projects.modal.detached_invoice')
    @include('focus.projects.modal.attach_di_quote')
    @include('focus.projects.modal.note_new')
    @include('focus.projects.modal.delete_2')
    @include('focus.projects.modal.task_update_progress')
    @if(access()->allow('create-task')) 
        @include('focus.projects.modal.task_new', ['project' => $project]) 
    @endif
@endsection

@section('after-styles')
    {{ Html::style('core/app-assets/css-'.visual().'/pages/project.css') }}
    {!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection

@section('after-scripts')
@include('focus.projects.view_js')
@endsection