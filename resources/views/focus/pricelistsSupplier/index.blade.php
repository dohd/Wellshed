@extends ('core.layouts.app')

@section ('title', 'Price List Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Price List Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.pricelistsSupplier.partials.pricelists-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => array('biller.pricelistsSupplier.destroy', 0), 'method' => 'DELETE']) }}
                            <div class="row">
                                @if(empty(Auth::user()->supplier_id))
                                    <div class="col-4">
                                        <label for="supplier">Supplier</label>
                                        <select name="supplier_id" id="supplier" class="form-control" data-placeholder="Choose Supplier" required>
                                            {{-- <option value="">-- select supplier --</option> --}}
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->company }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-2">
                                    <label for="contract">Contract</label>                             
                                    <select name="contract" id="contract" class="custom-select" disabled>
                                        <option value="">-- select contract --</option>
                                    </select>
                                </div>
                                @if(empty(Auth::user()->supplier_id))
                                    <div class="edit-form-btn">
                                        <label for="">&nbsp;</label>
                                        {{ Form::submit('Mass Delete', ['class' => 'form-control btn-danger mass-delete', 'disabled']) }}
                                    </div>
                                @endif
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
                                        <th>Supplier</th>
                                        <th>Product Code</th>
                                        <th>System Description</th>
                                        <th>Contract</th>
                                        <th>Row No.</th>
                                        <th>Supplier Description</th>
                                        <th>UoM</th>
                                        <th>Rate</th>
                                        <th>Status</th>
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
        suppliers: @json($suppliers),
        contracts: @json($contracts),

        init() {
            $.ajaxSetup(config.ajax);
            
            $('#supplier').select2({allowClear: true}).val('').change();
            this.drawDataTable();
            $('.mass-delete').click(this.massDelete);
            $('#supplier').change(this.supplierChange);
            $('#contract').change(this.contractChange);
        },

        massDelete() {
            event.preventDefault();
            if (!$('#supplier').val()) return alert('Supplier is required!');
            const form = $(this).parents('form');
            swal({
                title: 'Are You  Sure?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
                showCancelButton: true,
            }, () => form.submit());
        },

        supplierChange() {
            if ($(this).val()) {
                const contracts = Index.contracts.filter(v => v.supplier_id == $(this).val());
                $('#contract option:not(:eq(0))').remove();
                contracts.forEach(v => {
                    $('#contract').append(`<option value="${v.contract}" supplier_id="${v.supplier_id}" >${v.contract}</option>`);
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
                    url: '{{ route("biller.pricelistsSupplier.get") }}',
                    type: 'post',
                    data: {
                        supplier_id: $('#supplier').val(),
                        contract: $('#contract').val(),
                    }
                },
                columns: [
                    {
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'product_code',
                        name: 'product_code'
                    },
                    {
                        data: 'product_desc',
                        name: 'product_desc'
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
                        data: 'status',
                        name: 'status'
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
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });
        },
    };

    $(() => Index.init());
</script>
@endsection