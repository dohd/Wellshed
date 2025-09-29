@extends ('core.layouts.app')

@section('title', 'Bill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Create KRA Bill</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.utility-bills.partials.utility-bills-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.utility_bills.store_kra_bill', 'method' => 'POST']) }}
                        @include('focus.utility-bills.kra-form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        datepicker: {format: "{{config('core.user_date_format')}}", autoHide: true, },
    };

    const Form = {
        rowIndx: 0,
        tableRow: $('#billsTbl tbody tr:first').html(),

        init (config) {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#supplier').select2({allowClear: true});

            $('#billsTbl').on('change', '.amount', this.amountChange);
            $('#billsTbl').on('click', '.del', this.deleteRow);
            $('#addRow').click(this.addRow);
        },

        addRow() {
            this.rowIndx++;
            const i = this.rowIndx;
            const html = Form.tableRow.replace(/-0/g, '-'+i);
            $('#billsTbl tbody').append('<tr>' + html + '</tr>');
        },

        amountChange() {
            const el = $(this);
            el.val(accounting.formatNumber(el.val()));
            Form.columnTotals();
        },

        deleteRow() {
            $(this).parents('tr').remove();
            Form.columnTotals();
        },

        columnTotals() {
            let total = 0;
            $('#billsTbl tbody tr').each(function() {
                const el = $(this);
                let amount = accounting.unformat(el.find('.amount').val());
                total += amount;
            });
            $('#total').val(accounting.formatNumber(total));
        }
    }

    $(() => Form.init(config));
</script>
@endsection