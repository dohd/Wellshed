@extends ('core.layouts.app')

@section('title', 'CallList Management')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">CallList Management</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-auto float-right mr-3">
                    <div class="media-body media-right text-right">
                        @include('focus.prospects.partials.prospects-header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    
                    <div class="card">
                        <div class="card-header">
                            <div class="form-group row">
                                <div class="col-4">
                                    <label for="user">Search Title</label>
                                    <select name="title" id="title" class="form-control" data-placeholder="Search Title">
                                        <option value="">Search Title</option>
                                        @foreach ($call_lists as $call)
                                            <option value="{{$call}}">{{$call}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label for="user">Search User</label>
                                    <select name="employee_id" id="user" class="form-control" data-placeholder="Search User">
                                        <option value="">Search User</option>
                                        @foreach ($employees as $user)
                                            <option value="{{$user->id}}">{{$user->fullname}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <table id="calllist-table" class="table table-striped table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Prospects to Call</th>
                                            <th>Prospects Called</th>
                                            <th>Prospects Not Called</th>
                                            <th>Explore Prospects</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Assigned Employee</th>
                                            
                                            <th>{{ trans('labels.general.actions') }}</th>
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
    @include('focus.prospects.partials.remarks_modal')
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
        };

        const Index = {

            init() {
                $.ajaxSetup(config.ajax);
                this.draw_data();
                $('#user').select2({allowClear: true}).val('').trigger('change').change(this.userChange);
                $('#title').select2({allowClear: true}).val('').trigger('change').change(this.titleChange);
            },

            userChange(){
                $('#calllist-table').DataTable().destroy();
                return Index.draw_data();
            },
            titleChange(){
                $('#calllist-table').DataTable().destroy();
                return Index.draw_data();
            },
          

            draw_data(params = {}) {
                $('#calllist-table').dataTable({
                    stateSave: true,
                    processing: true,
                    responsive: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route('biller.calllists.get') }}',
                        type: 'post',
                        data: {
                            user_id: $('#user').val(),
                            title: $('#title').val(),
                            ...params,
                        },
                    },
                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        {
                            data: 'title',
                            name: 'title'
                        },
                        {
                            data: 'category',
                            name: 'category'
                        },
                        {
                            data: 'prospects_number',
                            name: 'prospects_number'
                        },
                        {
                            data: 'number_of_called',
                            name: 'number_of_called'
                        },
                        {
                            data: 'number_of_not_called',
                            name: 'number_of_not_called'
                        },
                        {
                            data: 'explore',
                            name: 'explore'
                        },
                        {
                            data: 'start_date',
                            name: 'start_date'
                        },
                        {
                            data: 'end_date',
                            name: 'end_date'
                        },
                        {
                            data: 'employee',
                            name: 'employee'
                        },
                       
                        {
                            data: 'actions',
                            name: 'actions',
                            searchable: false,
                            sortable: false
                        }
                    ],
                    columnDefs: [{
                        type: "custom-date-sort",
                        targets: [4,5]
                    }],
                    order: [
                        [0, "desc"]
                    ],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print'],
                });
            }
        };
        $(() => Index.init());
    </script>


@endsection
