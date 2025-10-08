@extends ('core.layouts.app')

@section ('title', 'Edit Order')

@section('page-header')
    <h1>
        <small>Edit Order</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit Order</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.customer_orders.partials.customer_orders-header-buttons')
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
                                    {{ Form::model($customer_order, ['route' => ['biller.customer_orders.update', $customer_order], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-department']) }}

                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.customer_orders.form")
                                    
                                    </div><!--form-group-->

                                    {{ Form::close() }}
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: {
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
            },
            branchSelect: {
                allowClear: true,
                ajax: {
                    url: "{{ route('biller.branches.select') }}",
                    dataType: 'json',
                    type: 'POST',
                    data: ({term}) => ({
                        search: term, 
                        customer_id: $("#customer").val()
                    }),
                    processResults: data => ({
                        results: data.map(v => ({text: v.name, id: v.id}))
                    }),
                }
            },
            itemSelect2: {
                allowClear: true,
                ajax: {
                    url: "{{ route('biller.products.purchase_search') }}",
                    dataType: 'json',
                    delay: 250,
                    type: 'POST',
                    data: ({ keyword }) => ({ keyword }),
                    processResults: response => {
                        return {
                            results: (response || []).map(v => {
                                const selling_price = accounting.unformat(v.price);
                                const purchase_price = accounting.unformat(v.purchase_price);
                                const available_qty = accounting.unformat(v.qty);

                                return {
                                    id: v.id,
                                    text: v.name,
                                    selling_price,
                                    purchase_price,
                                    available_qty,
                                };
                            })
                        };
                    },
                }
            },
        };

        function initItemSelect2($scope) {
            $scope.find('select.product_id')
                .not('.select2-hidden-accessible')
                .select2(Object.assign({}, config.itemSelect2, {
                    dropdownParent: $('body'),
                    width: '100%'
                }))
                .each(function () {
                    // Preload selected value (for edit)
                    let $el = $(this);
                    let selectedId = $el.data('selected-id');
                    let selectedText = $el.data('selected-text');
                    let selectedRate = $el.data('selected-rate');

                    if (selectedId && selectedText) {
                        let option = new Option(selectedText, selectedId, true, true);
                        $el.append(option).trigger('change');

                        // If rate column should be prefilled
                        let tr = $el.closest('tr');
                        if (selectedRate) {
                            tr.find('.rate').val(selectedRate);
                        }
                    }
                });
        }

        const Index = {
            init() {
                $.ajaxSetup(config.ajax);

                // customer dropdown
                $('#customer').select2({ allowClear: true });
                 $('#driver').select2({
                    allowClear: true
                });

                // branch dropdown
                $("#branch").select2(config.branchSelect);

                // initialize product dropdowns in existing rows
                initItemSelect2($('#itemsTbl'));

                // bind change handlers
                $('#itemsTbl').on('change', '.product_id', Index.onChangeProduct);

                $('#addRow').click(function () {
                    let $newRow = $($('#rowTemplate').html());
                    $('#itemsTbl tbody').append($newRow);
                    initItemSelect2($newRow);
                });

                $('#itemsTbl tbody tr:first').remove(); 
                $('#daysTbl tbody tr:first').remove(); 
                $(document).on('input change', '#itemsTbl .qty, #itemsTbl .rate, #itemsTbl .rowtax', function () {
                    Index.calculateAllTotals();
                });

                $('#itemsTbl').on('click', '.remove_doc', function () {
                    $(this).closest('tr').remove();
                    Index.calculateAllTotals();
                });

                let docRowId = 0;
                const docRow = $('#daysTbl tbody tr').html();
                $('#addDoc').click(function() {
                    docRowId++;
                    let html = docRow.replace(/-0/g, '-'+docRowId);
                    $('#daysTbl tbody').append('<tr>' + html + '</tr>');
                });
                // remove schedule row
                $('#daysTbl').on('click', '.remove', function() {
                    $(this).parents('tr').remove();
                    docRowId--;
                });

                // calculate totals on load (important for edit)
                Index.calculateAllTotals();
            },

            onChangeProduct() {
                const data = $(this).select2('data')[0];
                const tr = $(this).closest('tr');
                if (!data) return;
                tr.find('.rate').val(data.selling_price);
                Index.calculateAllTotals();
            },

            calculateLineTotals($row) {
                let qty = parseFloat($row.find('.qty').val()) || 0;
                let rate = parseFloat($row.find('.rate').val()) || 0;
                let vat = parseFloat($row.find('.rowtax').val()) || 0;

                let subtotal = qty * rate;
                let vatAmount = subtotal * (vat / 100);
                let lineTotal = subtotal + vatAmount;

                $row.find('.amt').text(lineTotal.toFixed(2));
                $row.find('.amount').val(lineTotal.toFixed(2));

                return { subtotal, vatAmount, lineTotal };
            },

            calculateAllTotals() {
                let subtotal = 0, totalTax = 0, grandTotal = 0;

                $('#itemsTbl tbody tr').each(function () {
                    let { subtotal: rowSubtotal, vatAmount, lineTotal } = Index.calculateLineTotals($(this));
                    subtotal += rowSubtotal;
                    totalTax += vatAmount;
                    grandTotal += lineTotal;
                });

                $('#subtotal').val(subtotal.toFixed(2));
                $('#vatable').val(subtotal.toFixed(2));
                $('#tax').val(totalTax.toFixed(2));
                $('#total').val(grandTotal.toFixed(2));
            }
        };

        $(() => Index.init());
    </script>
@endsection
