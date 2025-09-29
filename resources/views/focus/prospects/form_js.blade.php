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
            prospect: @json(@$prospect),
            date: @json(@$remarks->reminder_date),
            remark: @json(@$remarks->remarks),
            id: @json(@$remarks->id),
            init() {
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                if (this.prospect) {
                    $('#reminder_date').datepicker('setDate', new Date(this.date));
                    $('#remarks').val(this.remark);
                    $('#remark_id').val(this.id);
                
                } else {
                    $('#remarks').val('');
                    $('#remark_id').val('');
                    $('#reminder_date').datepicker('setDate', new Date());
                }

            },



        };

        $(() => Form.init());
    </script>
@endsection
