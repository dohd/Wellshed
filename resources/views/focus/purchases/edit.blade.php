@extends('core.layouts.app')
@section('title', 'Expense | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Expense Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchases.partials.purchases-header-buttons')
                </div>
            </div>
        </div>
    </div>    

    <div class="content-body"> 
        {{ Form::model($purchase, ['route' => ['biller.purchases.update', $purchase], 'method' => 'PATCH']) }}
            @include('focus.purchases.form')
        {{ Form::close() }}
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
@include('focus.purchases.form-js')
<script>
    $(() => {
        // reference and tax
        $('#ref_type').val("{{ $purchase->doc_ref_type }}");
        $('#tax').val("{{ $purchase->tax }}");
        
        // supplier type
        const supplierType = "{{ $purchase->supplier_type }}";
        if (supplierType == 'supplier') $('#colorCheck3').attr('checked', true);
    
        // date
        $('#date').datepicker('setDate', new Date("{{ $purchase->date }}"));
        $('#due_date').datepicker('setDate', new Date("{{ $purchase->due_date }}"));
    
        // supplier
        const supplierText = "{{ $purchase->suppliername? $purchase->suppliername : $purchase->supplier->name }} : ";
        const supplierVal = "{{ $purchase->supplier_id }}-{{ $purchase->supplier_taxid? $purchase->supplier_taxid : @$purchase->supplier->taxid }}";
        if (supplierType == 'supplier') $('#supplierbox').append(new Option(supplierText, supplierVal, true, true)).change();
    
        // project
        @php
            $project_name = '';
            $project = $purchase->project;
            if ($project) {
                $tid = gen4tid('Prj-', $project->tid);
                $customer = '';
                if ($project->customer_project) $customer = $project->customer_project->company;
                if ($customer && $project->branch) $customer .= " - {$project->branch->name}";
                $project_name = "{$customer} - {$tid} - {$project->name}";
            }
        @endphp
        const projectName = "{{ $project_name }}";
        const projectId = "{{ $purchase->project_id }}";
        if (projectId > 0) $('#project').append(new Option(projectName, projectId, true, true));
        const purchase_class_id = "{{ $purchase->purchase_class_budget }}";
        if (purchase_class_id > 0) $('#purchaseClass').change();
        
        // expense tab row 1
        let rowItems = @json($purchase->products);
        rowItems = rowItems.filter(v => v.type == 'Expense');
    
        if(rowItems.length) $('#projectexptext-0').val(rowItems[0]['project']?.name);
        
        // if amount is tax exclusive
        const isTaxExc =  @json($purchase->is_tax_exc);
        if (isTaxExc) {
            $('#tax_exc').change();
        } else {
            $('#tax_exc').prop('checked', false);
            $('#tax_inc').prop('checked', true).change();
        }
        $('#purchase_requisition_id').attr('disabled',true);

        // compute expenses
        calcExp();
    
        // classlists row 1
        $('#asset-classlist-id-1').select2({allowClear: true});
        $('#expense-classlist-id-1').select2({allowClear: true});
        $('#stock-classlist-id-1').select2({allowClear: true});
        // 
        $('#asset-class-budget-1').select2({allowClear: true});
        $('#expense-class-budget-1').select2({allowClear: true});
        $('#stock-class-budget-1').select2({allowClear: true});
        
        // set or unset budget line
        $('#stockTbl tbody tr').each(function() {
            if ($(this).find('.projectstock').val()) {
                $(this).find('.stock-class-budget').val('').prop('disabled',true);
                $(this).find('.select2:first').addClass('d-none');
                $(this).find('.stock-classlist').prop('disabled',true);
                $(this).find('.stock_purchase_class_budget').prop('disabled',false);
                $(this).find('.stock_classlist').prop('disabled',false);
            }else if($(this).find('.stock-class-budget').val())
            {
                $(this).find('.projectstock').prop('readonly',true);
                $(this).find('.stock-budgetline').prop('disabled',true);
                $(this).find('.stock_budget_line_id').prop('disabled',false);
            }
        });
        $('#expTbl tbody tr').each(function() {
            if ($(this).find('.projectexp').val()) {
                $(this).find('.expense-class-budget').val('').prop('disabled',true);
                $(this).find('.select2:first').addClass('d-none');
                $(this).find('.expense-classlist').prop('disabled',true);
                $(this).find('.exp_purchase_class_budget').prop('disabled',false);
                $(this).find('.exp_classlist').prop('disabled',false);
            }else if($(this).find('.expense-class-budget').val())
            {
                $(this).find('.projectexp').prop('readonly',true);
                $(this).find('.exp-budgetline').prop('disabled',true);
                $(this).find('.exp_budget_line_id').prop('disabled',false);
            }
        });
    });

</script>
@endsection
