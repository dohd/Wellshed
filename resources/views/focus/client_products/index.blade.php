@extends ('core.layouts.app')

@section ('title', 'Client Product Pricelist')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Client Product Pricelist</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.client_products.partials.clientproducts-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => array('biller.client_products.destroy', 0), 'method' => 'DELETE']) }}
                            <div class="row">
                                <div class="col-4">
                                    <label for="client">Customer</label>                             
                                    <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose Customer" required>
                                        {{-- <option value="">-- select customer --</option> --}}
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label for="contract">Contract</label>                             
                                    <select name="contract" id="contract" class="custom-select" disabled>
                                        <option value="">-- select contract --</option>
                                    </select>
                                </div>
                                <div class="edit-form-btn">
                                    <label for="">&nbsp;</label>
                                    {{ Form::submit('Mass Delete', ['class' => 'form-control btn-danger mass-delete', 'disabled']) }}
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="listTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th>Contract</th>
                                        <th>Row No.</th>
                                        <th>Product Code</th>
                                        <th>Product Description</th>
                                        <th>UoM</th>
                                        <th>Rate</th>
                                        <th>Action</th>
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
    @include('focus.client_products.modal.attach-inventory')
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}}
    };

    const Index = {
        customers: @json($customers),
        contracts: @json($contracts),
        inventoryUrl: "{{ route('biller.queuerequisitions.select') }}",

        init() {
            $.ajaxSetup(config.ajax);
            $('#customer').select2({allowClear: true}).val('').change();
            this.drawDataTable();
            $('#inventorybox').change(this.inventoryChange);
            $('#inventorybox').select2(this.select2Config(this.inventoryUrl, this.inventoryData));
            $('#listTbl').on('click', '.click', this.listChange);

            $('.mass-delete').click(this.massDelete);
            $('#customer').change(this.customerChange);
            $('#contract').change(this.contractChange);
        },

        massDelete() {
            event.preventDefault();
            if (!$('#customer').val()) return alert('customer is required!');
            const form = $(this).parents('form');
            swal({
                title: 'Are You  Sure?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
                showCancelButton: true,
            }, () => form.submit());
        },
        
         inventoryData(data) {
            return {results: data.map(v => ({id: v.id+'|'+v.code+'|'+v.qty+'|'+v.purchase_price, text: v.name+' : '+v.name}))};
        },

        inventoryChange() {
                const name = $('#inventorybox option:selected').text().split(' : ')[0];
                const [id, code, quantity,purchase_price] = $(this).val().split('|');
                $('#inventoryid').val(id);
                $('#inventory').val(name);
                $('#product_code').val(code);
                $('#purchase_price').val(accounting.formatNumber(purchase_price));
                $('#descr').val(name);
                $('#item_id').val(id);
                $('#item_qty').val(quantity);
         },
         listChange (e) {
                var id = e.target.getAttribute('data_id');
                $('#id').val(id);
                var client_uom = e.target.getAttribute('client-uom');
                $('#client_uom').val(client_uom);
                var client_rate = e.target.getAttribute('client-rate');
                $('#client_price').val(accounting.formatNumber(client_rate));
                // var quantity = e.target.getAttribute('quantity');
                // $('#item_qty').val(quantity);
        },
        
        select2Config(url, callback) {
                return {
                    ajax: {
                        url,
                        dataType: 'json',
                        type: 'POST',
                        quietMillis: 50,
                        data: ({term}) => ({q: term, keyword: term}),
                        processResults: callback
                    }
                }
         },

        customerChange() {
            if ($(this).val()) {
                const contracts = Index.contracts.filter(v => v.customer_id == $(this).val());
                $('#contract option:not(:eq(0))').remove();
                contracts.forEach(v => {
                    $('#contract').append(`<option value="${v.contract}" customer_id="${v.customer_id}" >${v.contract}</option>`);
                });
                $('.mass-delete').attr('disabled', false);
                $('#contract').attr('disabled', false).val('');
            } else {
                $('.mass-delete').attr('disabled', true);
                $('#contract').attr('disabled', true).val('');
            }

            $('#listTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        contractChange() {
            $('#listTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {       
            $('#listTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang("datatable.strings")},
                ajax: {
                    url: '{{ route("biller.client_products.get") }}',
                    type: 'post',
                    data: {
                        customer_id: $('#customer').val(),
                        contract: $('#contract').val(),
                    }
                },
                columns: [
                    {
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'contract',
                        name: 'contract'
                    },
                    {
                        data: 'row',
                        name: 'row'
                    },
                    {
                        data: 'product_code',
                        name: 'product_code'
                    },
                    {
                        data: 'descr',
                        name: 'descr'
                    },
                    {
                        data: 'uom',
                        name: 'uom'
                    },
                    {
                        data: 'rate',
                        name: 'rate'
                    },
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
                buttons: ['csv', 'excel', 'pdf'],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                pageLength: 10,
            });
        },
    };

    $(() => Index.init());
</script>
@endsection