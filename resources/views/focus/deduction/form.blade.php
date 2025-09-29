<div class="table-responsive">
    <table class="table text-center tfr my_stripe_single" id="productsTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white ">
                <th width="20%" class="text-center">Name</th>
                {{-- <th width="20%" class="text-center">Brackets</th> --}}
                <th width="10%" class="text-center">Amount From</th>
                <th width="10%" class="text-center">Amount To</th>
                <th width="10%" class="text-center">Rate</th>
                {{-- <th width="10%" class="text-center">Category</th> --}}
                <th width="10%" class="text-center">Actions</th>               
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <tr>
                <td>
                    {{-- <input type="text" class="form-control deductname" name="name[]" id="deductname-0"> --}}
                    <select class="form-control round deduct" name="name[]" id="deductname" data-placeholder="Select Deduction Type">
                        <option value="">Default</option>
                        <option value="NHIF">NHIF</option>
                        <option value="NSSF">NSSF</option>
                        <option value="PAYE">PAYE</option>
                    </select>
                </td>
                {{-- <td><input type="text" class="form-control bracket" id="bracket-0" name="brackets[]"></td>   --}}
                <td><input type="text" class="form-control from" id="amount_from-0" name="amount_from[]"></td> 
                <td><input type="text" class="form-control to" id="amount_to-0" name="amount_to[]"></td> 
                <td><input type="text" class="form-control rate" name="rate[]" id="rate-0" value="1"></td> 
                {{-- <td><input readonly type="text" class="form-control deduction_id" name="deduction_id[]" id="deduction_id-0"></td> --}}
                <td><button type="button" class="btn btn-danger remove" id="remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>


                @isset ($deductions)
                <tr>
                    <td>
                        {{-- <input type="text" class="form-control deductname" name="name[]" id="deductname-0"> --}}
                        <select class="form-control round deduct" name="name[]" id="deductname" data-placeholder="Select Deduction Type">
                            <option value="">Default</option>
                            <option value="NHIF" @isset($deductions)
                                {{$deductions->name == 'NHIF' ? 'selected':''}}
                            @endisset>NHIF</option>
                            <option value="NSSF" @isset($deductions)
                                    {{$deductions->name == 'NSSF' ? 'selected':''}}
                             @endisset>NSSF</option>
                            <option value="PAYE" @isset($deductions)
                                {{$deductions->name == 'PAYE' ? 'selected':''}}
                            @endisset>PAYE</option>
                        </select>
                    </td>
                    {{-- <td><input type="text" class="form-control bracket" id="bracket-0" value="{{ $deductions->brackets}}" name="brackets[]"></td>  --}}
                    <td><input type="text" class="form-control from" id="amount_from-0" value="{{ $deductions->amount_from}}" name="amount_from[]"></td> 
                    <td><input type="text" class="form-control to" id="amount_to-0" value="{{ $deductions->amount_to}}" name="amount_to[]"></td>  
                    <td><input type="text" class="form-control rate" name="rate[]" id="rate-0" value="{{$deductions->rate}}"></td> 
                    {{-- <td><input readonly type="text" class="form-control deduction_id" name="deduction_id[]" id="deduction_id-0"></td> --}}
                    <td><button type="button" class="btn btn-danger remove" id="remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    
                </tr>
                @endisset
            </tr>
        </tbody>
    </table>
</div>
<a href="javascript:" class="btn btn-success" aria-label="Left Align" id="addstock"><i class="fa fa-plus-square"></i> Add Row</a>


@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
        let tableRow = $('#productsTbl tbody tr:first').html();
    $('#productsTbl tbody tr:first').remove();
    let rowIds = 1;
    $('#addstock').click(function() {
        rowIds++;
        let i = rowIds;
        const html = tableRow.replace(/-0/g, '-'+i);
        $('#productsTbl tbody').append('<tr>' + html + '</tr>');
        $('#productsTbl').on('change','.deduct', deduct);
    });
    $('#productsTbl').on('click', '.remove', removeRow);
    function removeRow() {
        const $tr = $(this).parents('tr:first');
        $tr.next().remove();
        $tr.remove();
    }
    let rowId = 0;
    
    function deduct() {
        const name = $('#deductname option:selected').val();
        let i = rowId;
        if (name == "NHIF") {
            $('#deduction_id-'+i).val('1').change();
        }
        else if (name == 'NSSF') {
            $('#deduction_id-'+i).val('2').change();
        } else {
            $('#deduction_id-'+i).val('3').change();
        }
        
    }
    </script>
@endsection
