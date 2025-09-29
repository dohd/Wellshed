@section('after-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
        };

        const Form = {
            
            direct: @json(@$direct),
            excel: @json(@$excel),
            init() {
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#title').select2({
                    allowClear: true
                });
                $('#employee').select2({
                    allowClear: true
                });
               
                $('.prospect-type').change(this.prospectTypeChange);
                $('.prospect-count').text(this.direct);
                $('.direct-prospects').attr('hidden',false);
                $('#title').change(this.callListChange);
                let count = $('#directprospectcount').text();
                $('#prospects_number').val(count);
                $('#prospects_number').change(function(){
                    let excel_count = accounting.unformat($('#title option:selected').attr('count'));
                    let direct_count = accounting.unformat($('#directprospectcount').text());
                    let prospect_type = $('.prospect-type').val(); 
                    const new_prospect_number = accounting.unformat($(this).val());
                    
                    if(new_prospect_number > direct_count && typeof excel_count === "undefined"){
                        $('#prospects_number').val(direct_count);
                    }else if(new_prospect_number > excel_count)
                    {
                        $('#prospects_number').val(excel_count);
                    }
                    else{
                        $('#prospects_number').val(new_prospect_number);
                    }

                })
            },

           
            prospectTypeChange() {
                if ($(this).val() == 'direct') {
                    let count = $('#directprospectcount').text();
                    $('#prospects_number').val(count);
                    $('#title').attr('disabled', true).val('').change();
                    $('.direct-prospects').attr('hidden',false).change();
                    $('#prospects_number').attr('readonly',false);
                } else {
                    $('#title').attr('disabled', false).val('');
                    $('.direct-prospects').attr('hidden',true);
                    $('#prospects_number').attr('readonly',false);
                }
            },

            callListChange(){
                let count = $('#title option:selected').attr('count');
                
                $('#prospects_number').val(count);
            }

        };

        $(() => Form.init());
    </script>
@endsection
