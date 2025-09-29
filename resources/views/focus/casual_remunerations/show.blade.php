@extends ('core.layouts.app')
@include('tinymce.scripts')
@section ('title', "Casual Labourer Remuneration")

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Casual Labourer Remuneration</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.casual_remunerations.header-buttons')
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
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <tbody>
                                            @php
                                                $details = [
                                                    '#Serial' => gen4tid('CW-', $clR->tid),
                                                    'Title' => $clR->title,
                                                    'Date' => dateFormat($clR->date),
                                                    'Note' => $clR->description,
                                                    'Total Wage' => numberFormat($clR->total_amount),
                                                ];
                                            @endphp
                                            @foreach ($details as $key => $val)
                                                <tr>
                                                    <th>{{ $key }}</th>
                                                    <td>{!! $val !!}</td>
                                                </tr> 
                                            @endforeach      
                                            <tr>
                                                <th>Created By</th>
                                                <td>{{$clR->creator->first_name . " " . $clR->creator->last_name}}</td>
                                            </tr>      
                                            <tr>
                                                <th>Updated By</th>
                                                <td>{{$clR->updater->first_name . " " . $clR->updater->last_name}}</td>
                                            </tr>   
                                            <tr>
                                                <th>Approval Status</th>
                                                <td>
                                                    @if ($clR->status == 'PENDING')
                                                        <div class="badge" style="background-color: #BDBDBD;"> Pending </div>
                                                    @elseif ($clR->status == 'APPROVED')
                                                        <div class="badge" style="background-color: #81C784;"> Approved </div>
                                                    @elseif ($clR->status == 'ON HOLD')
                                                        <div class="badge" style="background-color: #FDD835;"> On Hold </div>
                                                    @else
                                                        <div class="badge" style="background-color: #b80000;"> Rejected </div>
                                                    @endif
                                                </td>
                                            </tr>  
                                            <tr>
                                                <th>Approved By</th>
                                                <td>{{ @$clR->approver->first_name . " " . @$clR->approver->last_name}}</td>
                                            </tr>   
                                            <tr>
                                                <th>Approval Note</th>
                                                <td>{!! $clR->approval_note !!}</td>
                                            </tr>  
                                        </tbody>
                                    </table>   
                                </div>

                                <!-- Approval form -->
                                <div class="col-md-6">
                                    @permission('approve-casual-labourers-remuneration')
                                        <fieldset class="border p-1 mb-2">
                                            <legend class="w-auto float-none h5">Approve Remuneration</legend>
                                            {{ Form::open(['route' => ['biller.clr-approval', $clR->clr_number], 'method' => 'POST']) }}
                                                <div class="row">
                                                    <div class="col-4 col-lg-4">
                                                        <label for="status">Approval Status</label>
                                                        <select id="status" name="status" class="custom-select">
                                                            <option value="">-- Select Status --</option>
                                                            @foreach(['APPROVED', 'ON HOLD', 'REJECTED'] as $st)
                                                                <option value="{{ $st }}" @if($clR->status === $st) selected @endif>{{ ucwords(strtolower($st)) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-4 col-lg-4">
                                                        <label for="account">Expense Account</label>
                                                        <select id="exp_account" name="exp_account_id" class="custom-select" required>
                                                            <option value="">-- Select Account --</option>
                                                            @foreach ($accounts as $account)
                                                                <option value="{{ $account->id }}" {{ $account->id == $clR->exp_account_id? 'selected' : '' }}>
                                                                    {{ $account->number }} - {{ $account->holder }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-12 mt-1">
                                                        <label for="approval_note">Note</label><br>
                                                        <textarea id="approval_note" name="approval_note" class="tinyinput" rows="4">{{ $clR->approval_note ?? '' }}</textarea>
                                                    </div>
                                                </div>
                                                <div>
                                                    <button type="submit" class="btn btn-large btn-blue mb-1 mt-2 float-right">
                                                        <i class="fa fa-check"></i> Update Approval
                                                    </button>                                                    
                                                </div>
                                            {{ Form::close() }}                                    
                                        </fieldset>
                                    @endauth
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
                            
                            <!-- Casual Wages Table -->                       
                            <p style="font-size: 20px" class="mb-0">Casuals' Wages</p>
                            <div class="table-responsive">
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
                            <div class="row mt-2">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    tinymce.init({
        selector: '.tinyinput',
        menubar: false,
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
        height: 200,
    });

    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Form = {
        wageItems: @json($wageItems),

        init() {
            $.ajaxSetup(config.ajax);

            $('#casualWagesTbl').on('keyup', '.wage, .dynamic-inp', Form.onKeyupCasualWageInput);
            $('#labourAllocation').change(Form.onChangelabourAllocation);

            setTimeout(() => {
                Form.drawJobCardsTable();
                Form.drawLabourHrsTable();                
            }, 500);
        },

        onKeyupCasualWageInput() {
            Form.computeTotals();
        },

        drawJobCardsTable() {
            let labourAllocationIds = @json($clR->labourAllocations->map(fn($v) => $v->id));
            $.ajax({
                url: "{{ route('biller.clr_job_card_details') }}", // Update with your server endpoint
                type: 'GET',
                data: {
                    laId: labourAllocationIds,
                },
                success: function (response) {
                    $('#jobCardsTable tbody').empty();
                    $.each(response, function(index, item) {
                        const date = item.date? item.date.split('-').reverse().join('-') : '';
                        var row = '<tr>' +
                            '<td>' + (date) + '</td>' +
                            '<td class="text-center"><b>' + item.link + '</b></td>' +
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

        drawLabourHrsTable() {
            const labourAllocationIds= @json($clR->labourAllocations->map(fn($v) => $v->id));
            $('#casualWagesTbl').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                scrollX: true,
                ajax: {
                    url: '{{ route("biller.labour_allocations.get_casual_wages") }}',
                    type: 'POST',
                    data: {
                        labour_allocation_id: labourAllocationIds,
                        clr_number: "{{ $clR->clr_number}}"
                    },
                    dataSrc: ({data}) => {
                        if (data.length) {
                            data = data.map(casual => {
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
                dom: 'Bfrt',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [[-1], ["All"]],
                pageLength: -1,
                initComplete: function(settings, json) {
                    Form.computeTotals();
                    // replace cell content
                    $(this)
                    .find('.wage, .dynamic-inp, .overtime-total, .wage-total, .wage-subtotal, .regular-total, .regular-subtotal')
                    .each(function() {
                        const value = accounting.unformat($(this).val());
                        const fValue = accounting.formatNumber(value);

                        const ri = $(this).parents('tr').index();
                        const ci = $(this).parents('td').index();
                        const table = $('#casualWagesTbl').DataTable();
                        if (table.row(ri).length) {
                            table.cell(ri, ci).data(fValue).draw(false);
                        }
                    });
                }
            });
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