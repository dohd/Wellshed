@extends ('core.layouts.app')

@section ('title', 'Salaries  | Set ')

@section('page-header')
    <h1>
      New Salary Salaries
        <small>Set</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Set New Salary</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.employeesalary.partials.employeesalary-header-buttons')
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
                                    {{ Form::open(['route' => 'biller.employeesalaries.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-allowance']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.employeesalary.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.employeesalaries.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
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
$('#user_id').select2({
        allowClear: true,
        placeholder: 'Search Employee by Name'
    }).val('').change();
    // initialize datepicker
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#referencedate').datepicker('setDate', new Date());
    $('#date').datepicker('setDate', new Date());

   // compute totals
   $("#allowanceTbl").on("change", ".amount", function() {
        let taxable = 0;
        let untaxable = 0;
        let bp_subtotal = 0;
   
        $("#allowanceTbl tbody tr").each(function(i) {
            const amount = accounting.unformat($(this).find('.amount').val(), accounting.settings.number.decimal);
            const is_taxable = $(this).find('.is_taxable').val();
            const type = $(this).find('.type').val();
            if (amount > 0) {
                
                if(is_taxable=='No'){

                    if(type=='Deduction'){
                        untaxable -= amount;
                    }else{
                        untaxable += amount;
                    }

                   

                }else{
                    if(type=='Deduction'){
                        taxable -= amount;
                    }else{
                        taxable += amount;
                    }

                }
             
            }
            
        });
         $('#total_taxable_allowance').val(accounting.formatNumber(taxable));
         $('#total_untaxable_allowance').val(accounting.formatNumber(untaxable));


         
          
    });
</script>
@endsection
