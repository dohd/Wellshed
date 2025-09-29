@extends ('core.layouts.app')

@section ('title', "Queue Requisition Management")

@section('page-header')
    <h1>{{ "Queue Requisition Management" }}</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'Queue Requisition Management' }}</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.queuerequisition.partials.queuerequisition-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-3">
                        <label for="quote" class="h4">Select Quote</label>
                        <select name="quote_id" id="quote" class="custom-select">
                            <option value="">-- select location --</option>
                            @foreach ($quotes as $quote)
                                <option value="{{ $quote->id }}">{{ $quote->tid }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    <div class="float-right">
                                        <a href="#" class="btn btn-primary transfer">Push to Purchase</a>
                                    </div>
                                    <table id="queuerequisition-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>#</th>
                                            <th>{{ 'Item Name' }}</th>
                                            <th>UOM</th>
                                            <th>{{ 'Qty Remains' }}</th>
                                            <th>{{ 'Quote No' }}</th>
                                            <th>{{ 'Client Branch' }}</th>
                                            <th>Status</th>
                                            <th>System Description</th>
                                            <th>Product Code</th>
                                            <th>Qty</th>
                                            <th>Add</th>
                                            <th>{{ trans('labels.general.actions') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td colspan="100%" class="text-center text-success font-large-1"><i
                                                        class="fa fa-spinner spinner"></i></td>
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
        @include('focus.queuerequisition.partials.add-supplier-list')
    </div>
@endsection

@section('extra-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }}
    };

   
    const Index = {
        quoteId: @json(request('quote_id')),
        supplierUrl: "{{ route('biller.queuerequisitions.select') }}",

        init() {
            this.drawDataTable();
            $('#quote').val(this.quoteId).change(this.quoteChange);
            $('#supplierbox').change(this.supplierChange);
            $('#supplierbox').select2(this.select2Config(this.supplierUrl, this.supplierData));
            $('#queuerequisition-table').on('click', '.click', this.queueChange);
            $('.transfer').click(this.requisition);
        },

        quoteChange() {
            Index.quoteId = $(this).val();
            $('#queuerequisition-table').DataTable().destroy();
            return Index.drawDataTable();
        },
        
        supplierData(data) {
            return {results: data.map(v => ({id: v.id+'-'+v.code+'-'+v.qty, text: v.name+' : '+v.name}))};
        },
        requisition(e){
        var checkedItems={};
        checkedItems.checkedId=[];
        checkedItems.uncheckedId=[];

        $("#queuerequisition-table input:checked").each(function(){
            var $this = $(this);

            if($this.is(":checked")){
                checkedItems.checkedId.push($this.attr("data-id"));
            }else{
                checkedItems.uncheckedId.push($this.attr("data-id"));
            }
        });

        $.ajax({
            method: "POST",
            url: "{{route("biller.queuerequisitions.status")}}",
            data: {
                statusId: checkedItems.checkedId
            },
            success: function (response) {
                location.reload();
            }
        });
        },

        supplierChange() {
                const name = $('#supplierbox option:selected').text().split(' : ')[0];
                const [id, code, quantity] = $(this).val().split('-');
                $('#supplierid').val(id);
                $('#supplier').val(name);
                $('#product_code').val(code);
                $('#item_qty').val(quantity);
         },

         queueChange (e) {
                var id = e.target.getAttribute('data-id');
                $('#id').val(id);
                var itemName = e.target.getAttribute('item-name');
                $('#descr').val(itemName);
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

        drawDataTable() {
            $('#queuerequisition-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.queuerequisitions.get") }}',
                    type: 'post',
                    data: {
                        quote_id: this.quoteId,
                        category_id: this.categoryId,
                        status: this.status
                    },
                },
                columns: [
                    {data: 'checkbox',  searchable: false,  sortable: false},
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'item_name', name: 'item_name'},
                    {data: 'uom', name: 'uom'},
                    {data: 'qty_balance', name: 'qty_balance'},
                    {data: 'quote_no', name: 'quote_no'},
                    {data: 'client_branch', name: 'client_branch'},
                    {data: 'status', name: 'status'},
                    {data: 'system_name', name: 'system_name'},
                    {data: 'product_code', name: 'product_code'},
                    {data: 'qty', name: 'qty'},
                    {data: 'button', name: 'button'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print']
            });
        },
    };    

    $(() => Index.init());
</script>
@endsection
