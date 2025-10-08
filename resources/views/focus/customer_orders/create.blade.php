@extends ('core.layouts.app')

@section('title', 'Create Order')

@section('page-header')
    <h1>
        <small>Create Order</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Order</h4>

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
                                    {{ Form::open(['route' => 'biller.customer_orders.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include('focus.customer_orders.form')
                                        {{-- <div class="edit-form-btn float-right">
                                            {{ link_to_route('biller.customer_orders.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                          
                                        </div><!--edit-form-btn--> --}}
                                    </div><!-- form-group -->

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
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            branchSelect: {
                allowClear: true,
                ajax: {
                    url: "{{ route('biller.branches.select') }}",
                    dataType: 'json',
                    type: 'POST',
                    data: ({term}) => ({search: term, customer_id: $("#customer").val()}),
                    processResults: data => {
                        return { results: data.map(v => ({text: v.name, id: v.id})) }
                    },
                }
            },
            itemSelect2: {
                allowClear: true,
                ajax: {
                    url: "{{ route('biller.products.purchase_search') }}",
                    dataType: 'json',
                    delay: 250,
                    type: 'POST',
                    data: ({
                        keyword
                    }) => ({
                        keyword,
                    }),
                    processResults: response => {
                        return {
                            results: (response || []).map(v => {
                                const selling_price = accounting.unformat(v.price);
                                const purchase_price = accounting.unformat(v.purchase_price);
                                const available_qty = accounting.unformat(v.qty);

                                return {
                                    id: v.id,
                                    text: `${v.name}`,
                                    selling_price,
                                    purchase_price,
                                    available_qty,
                                };
                            })
                        }
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
                }));
        }

        const Index = {
            init() {
                $.ajaxSetup(config.ajax);
                // customer dropdown
                $('#customer').select2({
                    allowClear: true
                });
                $('#driver').select2({
                    allowClear: true
                });
                $("#branch").select2(config.branchSelect).change();

                // init first row
                initItemSelect2($('#itemsTbl'));

                // product change handler
                $('#itemsTbl').on('change', '.product_id', Index.onChangeProduct);

                // add new row from template
                $('#addRow').click(function() {
                    let $newRow = $($('#rowTemplate').html()); // clone template row
                    $('#itemsTbl tbody').append($newRow);
                    initItemSelect2($newRow);
                });

                $(document).on('input change', '#itemsTbl .qty, #itemsTbl .rate, #itemsTbl .rowtax', function() {
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

                // remove row
                $('#itemsTbl').on('click', '.remove_doc', function() {
                    $(this).closest('tr').remove();
                    Index.calculateAllTotals();
                });
            },

            onChangeProduct() {
                const data = $(this).select2('data')[0];
                const tr = $(this).closest('tr');
                if (!data) return;
                console.log(data)
                tr.find('.rate').val(data.selling_price);
            },
            calculateLineTotals($row) {
                let qty = parseFloat($row.find('.qty').val()) || 0;
                let rate = parseFloat($row.find('.rate').val()) || 0;
                let vat = parseFloat($row.find('.rowtax').val()) || 0;

                // Subtotal per row (before VAT)
                let subtotal = qty * rate;

                // VAT amount per row
                let vatAmount = subtotal * (vat / 100);

                // Line total
                let lineTotal = subtotal + vatAmount;

                // Update row
                $row.find('.amt').text(lineTotal.toFixed(2));
                $row.find('.amount').val(lineTotal.toFixed(2));

                return {
                    subtotal,
                    vatAmount,
                    lineTotal
                };
            },

            calculateAllTotals() {
                let subtotal = 0;
                let totalTax = 0;
                let grandTotal = 0;

                $('#itemsTbl tbody tr').each(function() {
                    let {
                        subtotal: rowSubtotal,
                        vatAmount,
                        lineTotal
                    } = Index.calculateLineTotals($(this));

                    subtotal += rowSubtotal;
                    totalTax += vatAmount;
                    grandTotal += lineTotal;
                });

                // update totals section
                $('#subtotal').val(subtotal.toFixed(2));
                $('#vatable').val(subtotal.toFixed(2)); // taxable = subtotal
                $('#tax').val(totalTax.toFixed(2));
                $('#total').val(grandTotal.toFixed(2));
            },


        };

        $(() => Index.init());
    </script>
@endsection
