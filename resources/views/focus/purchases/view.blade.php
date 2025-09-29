@extends ('core.layouts.app')
@section ('title', 'Expense Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>Expense Summary</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchases.partials.purchases-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">    
            <div class="card-header pb-0 pt-0">
                <div class="btn-group">
                    @if (config('services.efris.base_url'))
                        <a href="#" class="btn btn-info mb-1 ml-1" id="stockAdjustmentBtn" data-toggle="modal" data-target="#stockAdjustmentModal">
                            <i class="fa fa-check-square-o" aria-hidden="true"></i> ETR Purchase Stock Adjustment
                        </a>
                    @endif
                </div>
            </div>        
            <!-- Purchase details -->
            <div class="row mb-2">
                <div class="col-md-6">
                    <table id="customer-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>   
                            @php
                                $project = $purchase->project ? gen4tid('Prj-', $purchase->project->tid) . '; ' . @$purchase->project->name : '';
                                $purchase_details = [
                                    'Serial No.' => gen4tid('DP-', $purchase->tid),
                                    'Supplier' => ($purchase->suppliername? $purchase->suppliername : $purchase->supplier)? @$purchase->supplier->name : '',
                                    'Tax PIN' => $purchase->supplier_taxid,
                                    'Expense Date' => dateFormat($purchase->date),
                                    'Due Date' => dateFormat($purchase->due_date),
                                    'Reference' => $purchase->doc_ref_type . ' - ' . $purchase->doc_ref,
                                    'Project' => $project,
                                    'Note' => $purchase->note,
                                ];
                            @endphp   
                            @foreach ($purchase_details as $key => $val)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr>
                            @endforeach                                                
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table id="customer-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>                                       
                            <tr>
                                <th>Inventory Cost</th>
                                <td>{{ amountFormat($purchase->stock_grandttl) }}</td>
                            </tr>  
                            <tr>
                                <th>Expense/Asset Cost</th>
                                <td>{{ amountFormat($purchase->expense_grandttl) }}</td>
                            </tr>  
                            <tr>
                                <th>Asset Equipment Cost</th>
                                <td>{{ amountFormat($purchase->asset_grandttl) }}</td>
                            </tr>  
                            <tr>
                                <th>Total Cost</th>
                                <td>{{ amountFormat($purchase->grandttl) }}</td>
                            </tr>                           
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Inventory / stock -->
            @if ($purchase->products->where('type', 'Stock')->count())
                <fieldset class="border p-1 mb-3">
                    <legend class="w-auto float-none h3">Inventory Items</legend>
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>#</th>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Tax</th>
                            <th>Tax Rate</th>
                            <th>Amount</th>
                            <th>Project</th>
                        </tr>
                        <tbody>
                            @foreach ($purchase->products->where('type', 'Stock') as $i =>$item)
                                <tr>
                                    <th>{{ $loop->iteration }}</th>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ number_format($item->qty, 1) }}</td>
                                    <td>{{ numberFormat($item->rate) }}</td>
                                    <td>{{ (int) $item->itemtax }}%</td>
                                    <td>{{ numberFormat($item->taxrate) }}</td>
                                    <td>{{ numberFormat($item->amount) }}</td>
                                    <td>
                                        @if($item->project)
                                            {{ gen4tid('Prj-', $item->project->tid) }} - {{ $item->project->name }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </fieldset>
            @endif

            <!-- Expense/Asset -->
            @if ($purchase->products->where('type', 'Expense')->count())
                <fieldset class="border p-1 mb-3">
                    <legend class="w-auto float-none h3">Expense/Asset Items</legend>
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>#</th>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Tax</th>
                            <th>Tax Rate</th>                            
                            <th>Amount</th>
                            <th>Ledger Account</th>
                            <th>Project</th>
                        </tr>
                        <tbody>
                            @foreach ($purchase->products->where('type', 'Expense') as $i => $item)
                                <tr>
                                    <th>{{ $loop->iteration }}</th>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ number_format($item->qty, 1) }}</td>
                                    <td>{{ numberFormat($item->rate) }}</td>
                                    <td>{{ (int) $item->itemtax }}%</td>
                                    <td>{{ numberFormat($item->taxrate) }}</td>
                                    <td>{{ numberFormat($item->amount) }}</td>
                                    <td>{{ $item->account? $item->account->holder : '' }}</td>
                                    <td>
                                        @if($item->project)
                                            {{ gen4tid('Prj-', $item->project->tid) }} - {{ $item->project->name }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </fieldset>
            @endif

            <!-- Asset/Equipment -->
            @if ($purchase->products->where('type', 'Asset')->count())
                <fieldset class="border p-1 mb-3">
                    <legend class="w-auto float-none h3">Asset/Equipment Items</legend>
                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>#</th>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Tax</th>
                            <th>Tax Rate</th>                            
                            <th>Amount ({{ $purchase->is_tax_exc? 'VAT Exc' : 'VAT Inc' }})</th>
                            <th>Ledger Account</th>
                            <th>Project</th>
                        </tr>
                        <tbody>
                            @foreach ($purchase->products->where('type', 'Asset') as $i => $item)
                                <tr>
                                    <th>{{ $loop->iteration }}</th>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ number_format($item->qty, 1) }}</td>
                                    <td>{{ numberFormat($item->rate) }}</td>
                                    <td>{{ (int) $item->itemtax }}%</td>
                                    <td>{{ numberFormat($item->taxrate) }}</td>
                                    <td>{{ numberFormat($item->amount) }}</td>
                                    <td>{{ $item->account? $item->account->holder : '' }}</td>
                                    <td>
                                        @if($item->project)
                                            {{ gen4tid('Prj-', $item->project->tid) }} - {{ $item->project->name }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </fieldset>
            @endif
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div id="stockAdjustmentModal" class="modal fade">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">EFRIS Purchase Stock Adjustment</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <!-- body loaded via ajax-->
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
    };

    const View = {
        init() {
            $.ajaxSetup(config.ajax);
            $(document).on('click', '#submitStockAdjustment', View.clickSubmitStockAdjustment);

            $('#stockAdjustmentModal').on('shown.bs.modal', View.onShownModal);
        },

        onShownModal() {
            $.post("{{ route('biller.products.efris_goods_adj_modal') }}", {purchase_id: "{{ $purchase->id }}"})
            .then(html => {
                $('#stockAdjustmentModal .modal-body').html(html);
            })
            .fail((xhr,status,error) => console.log(error));
        },

        clickSubmitStockAdjustment(e) {
            $('#stockAdjustmentModal').modal('hide');
            swal({
                title: 'Are You  Sure?',
                text: "Once applied, you will not be able to undo!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((isOk) => {
                if (isOk) {
                    addObject({
                        url: "{{ route('biller.products.efris_goods_adjustment') }}",
                        form: $("#goodsAdjForm").serialize(),
                    }, true);
                }
            }); 
        },
    };

    $(View.init);
</script>
@endsection
