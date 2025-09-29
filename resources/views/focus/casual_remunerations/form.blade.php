@include('tinymce.scripts')

@csrf
@if(isset($casualLabourersRemuneration))
    @method('PUT')
@endif
<div class="row mb-2">
    <div class="col-12 col-lg-1">
        <label for="serial">#Serial</label>
        <input type="text" class="form-control" value="{{ gen4tid('CW-', $tid) }}" disabled>
    </div>
    <div class="col-12 col-lg-2">
        <label for="date">Date</label>
        <input type="text" id="date" name="date" class="form-control datepicker" value="{{ isset($casualLabourersRemuneration) ? $casualLabourersRemuneration->date : '' }}" required>
    </div>
    <div class="col-12 col-lg-6 mb-2">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" class="form-control" value="{{ isset($casualLabourersRemuneration) ? $casualLabourersRemuneration->title : '' }}" required>
    </div>    
</div>
<div class="row mb-2">
    <div class="col-12 col-lg-10">
        <label for="description">Note</label>
        <br>
        <textarea id="description" name="description" class="form-control tinyinput" rows="1">{{ isset($casualLabourersRemuneration) ? $casualLabourersRemuneration->description : '' }}</textarea>
    </div>
</div>
<div class="row mb-2" style="max-height: 60vh; overflow-y: scroll;">
    <div class="col-md-10">
        <label for="labourAllocation">Select Reference</label>
        <select id="labourAllocation" name="labour_allocation_id[]" class="form-control" data-placeholder="Select a Labour Allocation" multiple>
            <option value=""></option>
            @foreach($labourAllocations as $lA)
                <option value="{{ $lA->id }}" {{ @$labourAllocationIdsArray && in_array($lA->id, $labourAllocationIdsArray)? 'selected' : '' }}>
                    {{ 'Job Card: ' . $lA['job_card'] . " | " . $lA['note'] }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<!-- Job-cards / DNote Reference Table -->
<div class="table-responsive mb-2" style="max-height: 60vh; overflow-y: scroll;">
    <h5>Labour Allocation Reference</h5>
    <table id="jobCardsTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>Date</th>
            <th>Jobcard/Dnote Ref</th>
            <th>Project</th>
            <th>Quote</th>
            <th>Lead</th>
            <th>Customer</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Casuals Wages Table -->
<div class="table-responsive mb-2">
    <p style="font-size: 20px" class="mb-0 ml-1">Casuals' Wages</p>
    <table id="casualWagesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>ID No.</th>
            <th>Full Name</th>
            <th>Phone No.</th>
            <th>OT Hrs.</th>
            <th>Reg Hrs.</th>
            <th>Total Hrs.</th>
            <th>Wage / Hr.</th>
            <th>OT Multiplier</th>
            <th>OT. Total</th>
            <th>Reg. Total</th>
            <th>Wage Subtotal</th>
            @foreach ($wageItems as $item)
                <th>{{ $item->name }}</th>
            @endforeach
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
        </tr>
        </tbody>
    </table>                    
</div>

<!-- Wages Summary -->
<div class="row mt-2 mb-2">
    <div class="col-md-4">
        <div class="table-responsive">
            <table id="wagesSummaryTbl" class="table table-bordered">
                <thead>
                    <th colspan="2" class="text-center">Summary</th>    
                </thead>
                <tbody>
                    <tr>
                        <td><b>Regular</b></td>
                        <td>0.00</td>
                    </tr>
                    <tr>
                        <td><b>Overtime</b></td>
                        <td>0.00</td>
                    </tr>
                    @foreach ($wageItems as $item)
                        <tr>
                            <td><b>{{ $item->name }}</b></td>
                            <td class="{{$item->earning_type}}-{{$item->id}}">0.00</td>
                        </tr>                                    
                    @endforeach
                    <tr>
                        <td><b>Total</b></td>
                        <td class="font-weight-bold">0.00</td>
                        <input type="hidden" name="total_amount" id="totalAmt">
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>  

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Form = {
        wageItems: @json($wageItems),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);
            $("#labourAllocation").select2({allowClear: true});

            if ($('#date').val()) $('#date').datepicker('setDate', new Date($('#date').val()));
            else $('#date').datepicker('setDate', new Date());

            $('#casualWagesTbl').on('keyup', '.wage, .dynamic-inp', Form.onKeyupCasualWageInput);
            $('#labourAllocation').change(Form.onChangelabourAllocation);

            // $(document).on('change', '#isJobcardCasual, #isWeeklyCasual', Form.onChangeCasualsRadioCheck);
            $('#searchCasuals').click(Form.onSearchCasuals);

            Form.drawLabourHrsTable();
            Form.drawJobCardsTable();

            /** Edit Mode*/
            const data = @json(@$casualLabourersRemuneration);
            if (data && data.clr_number) {
                if (data.period_from && data.period_to) {
                    $('#periodFrom').datepicker('setDate', new Date(data.period_from));
                    $('#periodTo').datepicker('setDate', new Date(data.period_to));
                    // 
                    $('#isJobcardCasual').parents('.form-check').addClass('d-none');
                    $('#isWeeklyCasual').prop('checked', true).change();
                } else {
                    $('#isWeeklyCasual').parents('.form-check').addClass('d-none');
                    $('#isJobcardCasual').prop('checked', true).change();
                }
            }
        },

        onSearchCasuals() {
            const isPeriod = $('#periodFrom').val() && $('#periodTo').val();
            if (!isPeriod) $('#periodFrom, #periodTo').val('');
            $('#casualWagesTbl').DataTable().destroy()
            return Form.drawLabourHrsTable();
        },

        onChangeCasualsRadioCheck() {
            if ($(this).is('#isJobcardCasual')) {
                if ($(this).prop('checked')) {
                    $('#jobCardsTable').parents('.table-responsive:first').removeClass('d-none');
                    $('#labourAllocation').parents('.row:first').removeClass('d-none');
                    $('#periodFrom').parents('.form-row:first').addClass('d-none');
                } else {
                    $('#jobCardsTable').parents('.table-responsive:first').addClass('d-none');
                    $('#labourAllocation').parents('.row:first').addClass('d-none');
                }
                $('#periodFrom, #periodTo').val('').change();
                $('#searchCasuals').click();
            }
            if ($(this).is('#isWeeklyCasual')) {
                if ($(this).prop('checked')) {
                    $('#periodFrom').parents('.form-row:first').removeClass('d-none');
                    $('#jobCardsTable').parents('.table-responsive:first').addClass('d-none');
                    $('#labourAllocation').parents('.row:first').addClass('d-none');
                } else {
                    $('#periodFrom').parents('.form-row:first').addClass('d-none');
                }
                $('#labourAllocation').val([]).change();
            }
        },

        onKeyupCasualWageInput() {
            Form.computeTotals();
        },

        setDynamicItemValues(data=[]) {
            return data.map(casual => {
                Form.wageItems.forEach((wageItem, i) => {
                    const {clr_wage_items} = wageItem;
                    if (clr_wage_items && clr_wage_items.length) {
                        clr_wage_items.forEach(clrWageItem => {
                            if (clrWageItem.casual_labourer_id == casual.id) {
                                if (wageItem.id == clrWageItem.wage_item_id) {
                                    casual[wageItem.name] = `
                                        <input type="hidden" name="wage_item_cl_id[]" value="${casual.id}">
                                        <input type="hidden" name="wage_item_id[]" value="${wageItem.id}">
                                        <input type="text" name="wage_item_total[]" value="${accounting.unformat(clrWageItem.wage_item_total)}" class="form-control dynamic-inp ${wageItem.earning_type}-${wageItem.id}">
                                    `;   
                                }
                            }
                        });
                    } else {
                        casual[wageItem.name] = `
                            <input type="hidden" name="wage_item_cl_id[]" value="${casual.id}">
                            <input type="hidden" name="wage_item_id[]" value="${wageItem.id}">
                            <input type="text" name="wage_item_total[]" class="form-control dynamic-inp ${wageItem.earning_type}-${wageItem.id}">
                        `;                                        
                    }
                });
                return casual;
            });
        },

        drawLabourHrsTable() {
            $('#casualWagesTbl').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                responsive: false,
                scrollX: true,
                ajax: {
                    url: '{{ route("biller.labour_allocations.get_casual_wages") }}',
                    type: 'POST',
                    data: {
                        labour_allocation_id: $("#labourAllocation").val(),
                        clr_number: "{{ @$casualLabourersRemuneration->clr_number}}",
                        period_from: $('#periodFrom').val(),
                        period_to: $('#periodTo').val(),
                    },
                    dataSrc: ({data}) => {
                        if (data.length) {
                            data = Form.setDynamicItemValues(data);
                        }
                        return data;
                    },
                },
                columns: [
                    { data: 'id_number', name: 'id_number', sortable: false},
                    { data: 'name', name: 'name', sortable: false},
                    { data: 'phone_number', name: 'phone_number', sortable: false},
                    { data: 'overtime_hrs', name: 'overtime_hrs', sortable: false},
                    { data: 'regular_hrs', name: 'regular_hrs', sortable: false},
                    { data: 'total_hrs', name: 'total_hrs', sortable: false}, // total hours
                    { data: 'wage', name: 'wage', sortable: false},
                    { data: 'ot_multiplier', name: 'ot_multiplier', sortable: false},
                    { data: 'overtime_total', name: 'overtime_total', sortable: false},
                    { data: 'regular_total', name: 'regular_total', sortable: false},
                    { data: 'wage_subtotal', name: 'wage_subtotal', sortable: false},
                    ...Form.wageItems.map(v => ({ data: `${v.name}`, name: `${v.name}`, sortable: false})),
                    { data: 'wage_total', name: 'wage_total', sortable: false},
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'frt',
                buttons: [],
                lengthMenu: [[-1], ["All"]],
                pageLength: -1,
                drawCallback: function() {
                    Form.computeTotals();
                }
            });
        },

        drawJobCardsTable() {
            $.ajax({
                url: "{{ route('biller.clr_job_card_details') }}", // Update with your server endpoint
                type: 'GET',
                data: {
                    laId: $('#labourAllocation').val(),
                },
                success: function (response) {
                    $('#jobCardsTable tbody').empty();
                    $.each(response, function(index, item) {
                        const date = item.date? item.date.split('-').reverse().join('-') : '';
                        var row = '<tr>' +
                            '<td>' + (date) + '</td>' +
                            '<td><b>' + item.link + '</b></td>' +
                            '<td>' + item.project + '</td>' +
                            '<td>' + item.quote + '</td>' +
                            '<td>' + item.lead + '</td>' +
                            '<td>' + item.customer + '</td>' +
                            '</tr>';
                        $('#jobCardsTable tbody').append(row);
                    });
                },
                error: function (xhr, status, error) {
                    // Parse the error response
                    const errorData = xhr.responseJSON;
                    if (errorData) {
                        console.table({
                            message: errorData.message,
                            code: errorData.code,
                            file: errorData.file,
                            line: errorData.line
                        });
                    } else {
                        console.error('Error fetching data:', error);
                    }
                }
            });
        },

        onChangelabourAllocation() {
            $('#casualWagesTbl').DataTable().destroy();
            Form.drawJobCardsTable()
            Form.drawLabourHrsTable();
        },

        computeTotals() {
            let colRegularTtl = 0;
            let colOvertimeTtl = 0;
            const dynamicInpObj = {};
            Form.wageItems.forEach(v => {
                dynamicInpObj[`${v.earning_type}-${v.id}`] = 0;
            });

            $('#casualWagesTbl tbody tr').each(function() {
                const wagePerHr = accounting.unformat($(this).find('.wage').val());
                // regular
                const regularHrs = accounting.unformat($(this).find('.regular-hrs').val());
                const regularTtl = regularHrs * wagePerHr;
                colRegularTtl += regularTtl;
                $(this).find('.regular-total').val(regularTtl);
                $(this).find('.regular-total-txt').html(accounting.formatNumber(regularTtl));

                // overtime
                const OTMultiplier = accounting.unformat($(this).find('.ot-multiplier').val());
                const overtimeTtl = OTMultiplier * wagePerHr;
                colOvertimeTtl += overtimeTtl;
                $(this).find('.overtime-total').val(overtimeTtl);
                $(this).find('.overtime-total-txt').html(accounting.formatNumber(overtimeTtl));

                // subtotal
                const wageSubttl = regularTtl+overtimeTtl;
                $(this).find('.wage-subtotal').val(wageSubttl);
                $(this).find('.wage-subtotal-txt').html(accounting.formatNumber(wageSubttl));
                // total
                let dynamicInpTtl = 0; 
                $(this).find('.dynamic-inp').each(function() {
                    const value = accounting.unformat($(this).val());
                    const classList = $(this).attr('class')?.split(/\s+/) || [];
                    classList.forEach(v => {
                        if (v in dynamicInpObj) {
                            dynamicInpObj[v] += value;
                            dynamicInpTtl += value;
                        }
                    });
                });
                const wageTotal = wageSubttl+dynamicInpTtl;
                $(this).find('.wage-total').val(wageTotal);
                $(this).find('.wage-total-txt').html(accounting.formatNumber(wageTotal));
            });
            
            const tbody = $('#wagesSummaryTbl tbody');
            tbody.find('tr:eq(0) td:eq(1)').html(accounting.formatNumber(colRegularTtl));
            tbody.find('tr:eq(1) td:eq(1)').html(accounting.formatNumber(colOvertimeTtl));

            let grandTtl = 0
            tbody.find('tr:not(:last)').each(function() {
                // set dynamic values
                $(this).find('td:eq(1)').each(function() {
                    const classList = $(this).attr('class')?.split(/\s+/) || [];
                    const el = $(this);
                    classList.forEach(v => {
                        if (v in dynamicInpObj) {
                           el.html(accounting.formatNumber(dynamicInpObj[v]));
                        }
                    });
                });

                grandTtl += accounting.unformat($(this).find('td:eq(1)').html());
            })
            tbody.find('tr:last td:eq(1)').html(accounting.formatNumber(grandTtl));
            $('#totalAmt').val(grandTtl);
        },
    };

    $(Form.init);
</script>
@endsection
