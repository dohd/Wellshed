@extends ('core.layouts.app')
@section ('title', trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('labels.backend.accounts.management') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.accounts.partials.accounts-header-buttons')
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="row form-group">
                        <div class="col-2">
                            <select id="category" class="form-control custom-select">
                                <option value="">-- Select Account Type --</option>
                                <optgroup label="Asset">
                                    @foreach ($accountTypes->where('category', 'Asset') as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Expense">
                                    @foreach ($accountTypes->where('category', 'Expense') as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Income">
                                    @foreach ($accountTypes->where('category', 'Income') as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Liability">
                                    @foreach ($accountTypes->where('category', 'Liability') as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Equity">
                                    @foreach ($accountTypes->where('category', 'Equity') as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="accounts-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>System</th>  
                                        <th>#Account No</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Debit</th>
                                        <th>Credit</th> 
                                        <th>Balance</th>   
                                        <th>{{ trans('labels.general.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
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
    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
    }

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            $('#category').change(Index.categoryChange);
            Index.drawDataTable();
        },

        categoryChange() {
            $('#accounts-table').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#accounts-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                ajax: {
                    url: "{{ route('biller.accounts.get') }}",
                    type: 'POST',
                    data: {account_type_id: $('#category').val()}
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...['system_type', 'number', 'holder', 'account_type', 'debit', 'credit', 'balance'].map(v => ({data: v, name: v})),    
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    }

    $(Index.init);
</script>
@endsection