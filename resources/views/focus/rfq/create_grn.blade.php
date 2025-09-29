@extends ('core.layouts.app')

@section ('title', 'Purchase Order | Grn')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>Receive Purchase Order Goods</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchaseorders.partials.purchaseorders-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">  
            @php ($po = $purchaseorder)
            {{ Form::open(['route' => ['biller.purchaseorders.grn', $po], 'method' => 'POST']) }}          
                <div class="row">
                    <div class="form-group col-2">
                        <label for="lpo_no">LPO Number</label>
                        <input type="text" class="form-control" name="lpo_no" value="{{ gen4tid('PO-', $po->tid) }}" disabled>
                    </div>
                    <div class="form-group col-3">                                        
                        <label for="supplier">Supplier</label>
                        <input type="text" class="form-control" name="supplier" value="{{ $po->supplier->name }}" disabled>
                    </div>
                    <div class="form-group col-7">
                        <label for="project">Project</label>
                        <input type="text" class="form-control" name="project" value="{{ $po->project? $po->project->name : '' }}" disabled>
                    </div>
                </div>
                <div class="row">                    
                    <div class="form-group col-12">                                        
                        <label for="note">Note</label>
                        <input type="text" class="form-control" name="note" value="{{ $po->note }}" disabled>
                    </div>                    
                </div>
                <div class="row justify-content-center">
                    <div class="form-group col-4">
                        {{ Form::submit('Receive Goods', ['class' => 'btn btn-primary btn-lg block']) }}
                        <input type="hidden" name="grandtax" id="grandtax">
                        <input type="hidden" name="grandttl" id="grandttl">
                        <input type="hidden" name="paidttl" id="paidttl">
                    </div>
                </div>
                @include('focus.purchaseorders.partials.grn_tabs')
            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script>
    // datepicker
    $('.datepicker')
    .datepicker({ format: "{{config('core.user_date_format')}}"})    
    .datepicker('setDate', new Date())
    .change(function() { $(this).datepicker('hide') });

    // Quantity on change
    $('#stockTbl').on('change', '.qty', function() {
        calcTotal('stockTbl');
    });
    $('#expTbl').on('change', '.qty', function() {
        calcTotal('expTbl');
    });
    $('#assetTbl').on('change', '.qty', function() {
        calcTotal('assetTbl');
    });

    // calculate totals
    let stockSubttl = 0;
    let stockTax = 0;
    let stockGrn = 0;
    let expSubttl = 0;
    let expTax = 0;
    let expGrn = 0;
    let assetSubttl = 0;
    let assetTax = 0;
    let assetGrn = 0;
    function calcTotal(id) {
        $('#'+id+' tbody tr').each(function() {
            const qty = $(this).find('.qty').val() * 1;
            if (qty) {
                const poRate = $(this).find('.porate').val();
                const poTax = $(this).find('.potax').val();
                switch(id) {
                    case 'stockTbl':
                        stockSubttl = qty * poRate;
                        stockTax = qty * poTax;
                        stockGrn = qty*1;
                        break;
                    case 'expTbl':
                        expSubttl += qty * poRate;
                        expTax = qty * poTax;
                        expGrn = qty*1;
                        break;
                    case 'assetTbl':
                        assetSubttl = qty * poRate;
                        assetTax = qty * poTax;
                        assetGrn = qty*1;
                        break;
                }
            }
        });

        switch(id) {
            case 'stockTbl':
                $('#stock_grn').val(stockGrn);
                $('#stock_subttl').val(stockSubttl);
                $('#stock_tax').val(stockTax);
                $('#stock_grandttl').val(stockSubttl+stockTax);
                break;
            case 'expTbl':
                $('#expense_grn').val(expGrn);
                $('#expense_subttl').val(expSubttl);
                $('#expense_tax').val(expTax);
                $('#expense_grandttl').val(expSubttl+expTax);
                break;
            case 'assetTbl':
                $('#asset_grn').val(assetGrn);
                $('#asset_subttl').val(assetSubttl);
                $('#asset_tax').val(assetTax);
                $('#asset_grandttl').val(assetSubttl+assetTax);
                break;
        }
        
        $('#grandtax').val(stockTax + expTax + assetTax);
        $('#paidttl').val(stockSubttl + expSubttl + assetSubttl);
        const grandTtl = [$('#grandtax').val(), $('#paidttl').val()].reduce((prev, curr) => prev + curr*1, 0);
        $('#grandttl').val(grandTtl);
    }
</script>
@endsection