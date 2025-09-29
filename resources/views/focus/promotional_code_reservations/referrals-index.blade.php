@php use App\Models\financialYear\FinancialYear; @endphp
@extends('core.layouts.app')

@section('title', 'Promo Redeemable Codes')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-2">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">Promo Redeemable Codes</h4>

            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">

                    <div class="media-body media-right text-right">

                    </div>
                </div>
            </div>
        </div>
        <div class="card">

            <div class="card-body">

                <div class="card-content">

                    <div class="card-body">
                        <div class="mb-1">

                            <div class="row mb-2">


                                <div class="col-9 col-lg-3">
                                    <label for="promoCodeFilter" >Filter by Promo Code</label>
                                    <select id="promoCodeFilter" class="custom-select round select2 filter" data-placeholder="Select a Promo Code" >
                                        <option value="">Select a Promo Code</option>
                                        @foreach ($promoCodes as $code)
                                            <option value="{{ $code['id'] }}"> {{ $code->code }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-9 col-lg-3">
                                    <label for="tierFilter" >Filter by Tier</label>
                                    <select id="tierFilter" class="custom-select round select2 filter" data-placeholder="Select a Tier" >
                                        <option value="">Select a Tier</option>
                                        @foreach ([1, 2, 3] as $t)
                                            <option value="{{ $t }}"> {{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>




                                <div class="col-9 col-lg-3">
                                    <label for="fromDateFilter" >Filter from Date</label>
                                    <input type="date" id="fromDateFilter" class="form-control box-size filter" >
                                </div>

                                <div class="col-9 col-lg-3">
                                    <label for="toDateFilter" >Filter to Date</label>
                                    <input type="date" id="toDateFilter" class="form-control box-size filter" >
                                </div>

                                <div class="col-3">
                                    <button id="clearNonDateFilters" class="btn btn-facebook round mt-2" > Clear Non-Date Filters </button>
                                </div>

                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear All Filters </button>
                                </div>


                            </div>

                        </div>
                        <table id="sirTable"
                               class="table table-striped table-bordered zero-configuration" cellspacing="0"
                               width="100%">
                            <thead>
                            <tr>
                                <th>Redeemable Code</th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Promo Code</th>
                                <th>Contact</th>
                                <th>Tier 3</th>
                                <th>Tier 2</th>
                                <th>Tier 1</th>
                                <th>Actions</th>
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
    {{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
    {{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
    {{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
    {{ Html::script('focus/js/select2.min.js') }}


    <script>

        $(function () {
            setTimeout(function () {

                drawSirTable();
            }, {{config('master.delay')}});
        });

        const fromDateFilter = $('#fromDateFilter');
        const toDateFilter = $('#toDateFilter');
        const promoCodeFilter = $('#promoCodeFilter');
        const tierFilter = $('#tierFilter');

        const clearFilters = $('#clearFilters');
        const clearNonDateFilters = $('#clearNonDateFilters');

        $('.select2').select2();

        $('.filter').change(() => {
            $('#sirTable').DataTable().destroy();
            drawSirTable();
        })

        $('#promoCodeFilter').change(() => {

            tierFilter.val('').trigger('change');

            $('#sirTable').DataTable().destroy();
            drawSirTable();
        })

        clearFilters.click(() => {

            fromDateFilter.val('');
            toDateFilter.val('');
            promoCodeFilter.val('').trigger('change');
            tierFilter.val('').trigger('change');

            $('#sirTable').DataTable().destroy();
            drawSirTable();
        })

        clearNonDateFilters.click(() => {

            promoCodeFilter.val('').trigger('change');
            tierFilter.val('').trigger('change');

            $('#sirTable').DataTable().destroy();
            drawSirTable();
        })


        function drawSirTable() {

            $('#sirTable').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.get-referrals-table") }}',
                    type: 'get',
                    data: {

                        fromDateFilter: fromDateFilter.val(),
                        toDateFilter: toDateFilter.val(),
                        promoCodeFilter: promoCodeFilter.val(),
                        tierFilter: tierFilter.val(),
                    }
                },
                columns: [
                    {data: 'redeemable_code', name: 'redeemable_code'},
                    {data: 'date', name: 'date'},
                    {data: 'name', name: 'name'},
                    {data: 'promo_code', name: 'promo_code'},
                    {data: 'contact', name: 'contact'},
                    {data: 'tier_3', name: 'tier_3'},
                    {data: 'tier_2', name: 'tier_2'},
                    {data: 'tier_1', name: 'tier_1'},

                    {data: 'actions', name: 'actions'},
                    // Add other columns as needed
                ],
                order: [],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });


        }



    </script>
@endsection
