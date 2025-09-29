@extends ('core.layouts.app')

@section ('title', 'Edit RfQ Analysis')

@section('page-header')
    <h1>
        RfQ Analysis
        <small>Edit RfQ Analysis</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit RfQ Analysis</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.rfq_analysis.partials.rfq_analysis-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#supplierModal">
                                    <i class="fa fa-plus" aria-hidden="true"></i> Choose Supplier
                                </a>
                            </div>
                            <div class="card-content">

                                <div class="card-body">
                                    {{ Form::model($rfq_analysis, ['route' => ['biller.rfq_analysis.update', $rfq_analysis], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-department']) }}

                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.rfq_analysis.edit_form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.rfq_analysis.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
                                    </div><!--form-group-->

                                    {{ Form::close() }}
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.rfq_analysis.partials.supplier-modal')
    </div>
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
    <script>
        updateWinnerSupplier();
        $(document).on("input", ".price", function () {
            const $row = $(this).closest("tr");
            const $prices = $row.find(".price");
            let minPrice = Infinity;
            let lowestSupplier = "";

            // Determine the lowest supplier for the current row
            $prices.each(function () {
                const price = parseFloat($(this).val()) || Infinity;
                const supplierName = $(this).data("supplier-name");

                if (price < minPrice) {
                    minPrice = price;
                    lowestSupplier = supplierName;
                }
            });

            // Update the lowest-supplier select in the row
            const $lowestSupplierSelect = $row.find(".lowest-supplier");
            if (lowestSupplier) {
                $lowestSupplierSelect.val(lowestSupplier);
            } else {
                $lowestSupplierSelect.val("");
            }

            // Count occurrences of each lowest supplier
            const supplierCount = {};
            $(".lowest-supplier").each(function () {
                const selectedSupplier = $(this).val();
                if (selectedSupplier) {
                    supplierCount[selectedSupplier] = (supplierCount[selectedSupplier] || 0) + 1;
                }
            });

            // Get suppliers appearing multiple times
            const frequentSuppliers = Object.keys(supplierCount).filter(
                supplier => supplierCount[supplier] > 1
            );

            // Update the winner-supplier select options
            const $winnerSelect = $(".winner-supplier");
            $winnerSelect.empty().append('<option value="">Select Winner</option>');

            frequentSuppliers.forEach(supplier => {
                $winnerSelect.append(
                    `<option value="${supplier}">${supplier} (${supplierCount[supplier]} times)</option>`
                );
            });

            // Auto-select the most frequent supplier if available
            if (frequentSuppliers.length > 0) {
                const mostFrequentSupplier = frequentSuppliers.reduce((a, b) =>
                    supplierCount[a] > supplierCount[b] ? a : b
                );
                $winnerSelect.val(mostFrequentSupplier);
            }
        });

        $(document).on("change", ".lowest-supplier", function () {
            updateWinnerSupplier();
        });

        function updateWinnerSupplier() {
            // Count occurrences of each selected supplier
            const supplierCount = {};
            $(".lowest-supplier").each(function () {
                const selectedSupplier = $(this).val();
                if (selectedSupplier) {
                    supplierCount[selectedSupplier] = (supplierCount[selectedSupplier] || 0) + 1;
                }
            });

            // Get the suppliers that are tied or most frequent
            const suppliers = Object.keys(supplierCount);
            let maxCount = 0;
            const tiedSuppliers = [];

            suppliers.forEach(supplier => {
                const count = supplierCount[supplier];
                if (count > maxCount) {
                    maxCount = count;
                    tiedSuppliers.length = 0; // Clear previous ties
                    tiedSuppliers.push(supplier);
                } else if (count === maxCount) {
                    tiedSuppliers.push(supplier);
                }
            });

            // Update the winner-supplier select options
            const $winnerSelect = $(".winner-supplier");
            $winnerSelect.empty().append('<option value="">Select Winner</option>');

            // Populate all suppliers if there is a tie or no clear winner
            const allSuppliers = {!! json_encode($suppliers->pluck('company')) !!};
            const suppliersToDisplay = tiedSuppliers.length > 1 ? allSuppliers : tiedSuppliers;

            suppliersToDisplay.forEach(supplier => {
                const count = supplierCount[supplier] || 0;
                const displayText = count > 0 ? `${supplier} (${count} times)` : supplier;
                $winnerSelect.append(`<option value="${supplier}">${displayText}</option>`);
            });

            // Auto-select the winner if there is only one clear winner
            if (tiedSuppliers.length === 1) {
                $winnerSelect.val(tiedSuppliers[0]);
            }
        }
        const config = {
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        };
        const Form = {
            rfq_analysis: @json($rfq_analysis),
            init(){
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#date').datepicker('setDate', new Date(this.rfq_analysis.date))
                $('#supplier_id').select2({allowClear: true});
                function calculateAmountAndTotal() {
                    let supplierTotals = {};

                    $(".price").each(function() {
                        let $priceInput = $(this);
                        let $amountInput = $priceInput.closest("td").next().find(".amount"); // Find corresponding amount input
                        let quantity = parseFloat($priceInput.closest("tr").find(".quantity").text()) || 0; // Get quantity
                        let price = parseFloat($priceInput.val()) || 0;

                        // Calculate amount
                        let amount = quantity * price;
                        $amountInput.val(amount.toFixed(2));

                        // Extract supplier ID
                        let supplierId = $priceInput.attr("name").match(/\d+/)[0];

                        // Add to supplier total
                        supplierTotals[supplierId] = (supplierTotals[supplierId] || 0) + amount;
                    });

                    // Update total price per supplier
                    $(".supplier-total").each(function() {
                        let supplierId = $(this).attr("data-supplier-id");
                        $(this).text(supplierTotals[supplierId] ? supplierTotals[supplierId].toFixed(2) : "0.00");
                    });
                }

                // Trigger calculation on input change
                $(document).on("input", ".price", calculateAmountAndTotal);

                // Initial calculation on page load
                calculateAmountAndTotal();
            },
        };
        $(() => Form.init())
    </script>
@endsection