@extends ('core.layouts.app')

@section('title', 'Partial Verification Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Partial Job Verification</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.verifications.partials.verification-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => 'biller.invoices.filter_invoice_quotes', 'method' => 'GET']) }}
                            <input type="hidden" name="selected_products" value="" id="quote_ids">
                            <input type="hidden" name="is_part_verification" value="1">
                            <div class="row">                            
                                <div class="col-2">
                                    <div class="form-group pl-3" style="padding-top: .5em">
                                        {{ Form::submit('Invoice Selected', ['class' => 'btn btn-xs btn-success mt-2 add-selected']) }}
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label><strong>Customer :</strong></label>
                                        <select name="customer" id="customer" class="form-control select2" data-placeholder="Choose Customer" required>
                                            <option value=""></option>
                                            @foreach ($customers as $row)
                                                <option value="{{ $row->id }}">{{ $row->company }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <label><strong>LPO :</strong></label>
                                        <select name="lpo_id" id="lpo" class="form-control select2" data-placeholder="Choose Client LPO">
                                            <option value=""></option>
                                            @foreach ($lpos as $row)
                                                <option value="{{ $row->id }}" customer_id="{{ $row->customer_id }}">{{ $row->lpo_no }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label><strong>Project :</strong></label>
                                        <select name="project_id" id="project" class="form-control select2" data-placeholder="Choose Project">
                                            <option value=""></option>
                                            @foreach ($projects as $row)
                                                <option value="{{ $row->id }}" customer_id="{{ $row->customer_id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
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
                            <table id="verificationsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" name="" id="select-all"></th>
                                        <th>#Ver No.</th>
                                        <th>#Quote / PI</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>LPO No.</th>
                                        <th>Project No.</th>
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
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
            $('.select2').select2({allowClear: true});

            $('#verificationsTbl').on('change', '.select-row', this.selectRowChange);
            $('#select-all').change(this.selectAllChange);

            $('#customer').change(this.customerChange);
            $('#lpo').change(this.lpoChange);
            $('#project').change(this.projectChange);
            $('form').submit(this.formSubmit);

            this.drawDataTable();
        },

        formSubmit() {
            if (!$('#quote_ids').val()) {
                event.preventDefault();
                return swal('Select records before submission!');
            }
        },

        selectRowChange() {
            if ($('#customer').val()) {
                const quoteIds = $('#quote_ids').val()? $('#quote_ids').val().split(',') : [];
                if (this.checked) quoteIds.push(this.value);
                else quoteIds.splice(quoteIds.indexOf(this.value), 1);
                $('#quote_ids').val(quoteIds.join(','));
            } else {
                this.checked = false;
                swal('Filter records by customer before selection!');
            }
        },

        selectAllChange() {
            if ($('#customer').val()) {
                if (this.checked) {
                    $('.select-row').prop('checked', true);
                    const quoteIds = [];
                    $('.select-row').each(function() {
                        quoteIds.push($(this).val());
                    });
                    $('#quote_ids').val(quoteIds.join(','));
                } else {
                    $('.select-row').prop('checked', false);
                    $('#quote_ids').val('');
                }
            } else {
                this.checked = false;
                swal('Filter records by customer before selection!');
            }
        },

        lpoChange() {
            $('#verificationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        projectChange() {
            $('#verificationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        customerChange() {
            $('#verificationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#verificationsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.verifications.get') }}",
                    type: 'POST',
                    data: {
                        customer_id: $('#customer').val(),
                        lpo_id: $('#lpo').val(),
                        project_id: $('#project').val(),
                    }
                },
                columns: [
                    {data: 'checkbox',  searchable: false,  sortable: false},
                    ...['tid', 'quote_tid', 'customer', 'total', 'lpo_no', 'project_no'].map(v => ({data:v, name: v})),
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
