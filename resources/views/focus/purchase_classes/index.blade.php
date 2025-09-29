@extends ('core.layouts.app')

@section ('title', 'Non-Project Purchase Classes')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Non-Project Purchase Classes </h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchase_classes.partials.header-buttons')
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

                            <div class="row mb-2">
                                <div class="col-9 col-lg-6">
                                    <label for="purchaseOrderMonth" >Filter by Expense Category</label>
                                    <select class="form-control box-size filter" id="expenseCategory" name="expenseCategory" data-placeholder="Filter by Expense Category">

                                        <option value=""></option>
                                        @foreach ($expenseCategories as $eC)
                                            <option value="{{ $eC->id }}">
                                                {{ $eC->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                </div>
                            </div>

                            <table id="pcTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Expense Category</th>
                                        <th>Action</th>
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
{{ Html::script('focus/js/select2.min.js') }}

<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    const expenseCategoryFilter = $('#expenseCategory');

    expenseCategoryFilter.select2({ allowClear: true });

    $('.filter').change(() => {
        $('#pcTable').DataTable().destroy();
        draw_data();
    })

    const clearFilters = $('#clearFilters');


    clearFilters.click(() => {

        expenseCategoryFilter.val('').trigger('change');

        $('#pcTable').DataTable().destroy();
        draw_data();
    })


    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#pcTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.purchase-classes.index") }}',
                type: 'GET',
                data: {
                    expenseCategoryFilter: expenseCategoryFilter.val(),
                }
            },
            columns: [
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'expense_category',
                    name: 'expense_category'
                },
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [0, "asc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection