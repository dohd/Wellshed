@extends ('core.layouts.app')
@section ('title', 'Supplier Aging Summary')

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Supplier Aging Summary</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.suppliers.partials.suppliers-header-buttons')
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
                                <div class="row no-gutters">
                                    <div class="col-1 text-center">As Of Date</div>
                                    <div class="col-1 mr-1">
                                        <input type="text" name="start_date" value="{{ date('d-m-Y') }}" id="start_date" class="form-control form-control-sm datepicker">
                                        <input type="hidden" name="end_date" value="{{ date('d-m-Y', strtotime("2000-01-01")) }}" id="end_date" class="form-control form-control-sm ">
                                    </div>
                                    <div class="col-1">
                                        <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm">
                                    </div>
                                </div>
                                <hr>
                                <table id="agingTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Supplier Name</th>
                                            @foreach (['0 - 30', '31 - 60', '61 - 90', '91 - 120', '120+'] as $val)
                                                <th>{{ $val }}</th>
                                            @endforeach
                                            <th>Aging Total</th>  
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totals = array_fill(0, 5, 0); // Assuming there are 5 aging clusters
                                            $totalAgingTotal = 0;
                                        @endphp
                                        @foreach ($suppliers_data as $data)
                                            @php
                                                $supplier = $data['supplier'];
                                                $aging_cluster = $data['aging_cluster'];
                                                $total_aging = 0;
                                            @endphp
                                            <tr>
                                                <td><a href="{{ route('biller.suppliers.show', $supplier) }}">{{ $supplier->company ?: $supplier->name }}</a></td>
                                                @for ($i = 0; $i < count($aging_cluster); $i++) 
                                                    <td>{{ numberFormat($aging_cluster[$i]) }}</td>
                                                    @php
                                                        $total_aging += $aging_cluster[$i];
                                                        $totals[$i] += $aging_cluster[$i];
                                                    @endphp
                                                @endfor
                                                <td>{{ numberFormat($total_aging) }}</td>
                                                @php
                                                    $totalAgingTotal += $total_aging;
                                                @endphp
                                            </tr>
                                        @endforeach                    
                                    </tbody> 
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            @for ($i = 0; $i < count($totals); $i++)
                                                <th>{{ numberFormat($totals[$i]) }}</th>
                                            @endfor
                                            <th>{{ numberFormat($totalAgingTotal) }}</th>
                                        </tr>
                                    </tfoot>                    
                                </table> 
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
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Form = {
        init(){
            $.ajaxSetup(config.ajaxSetup);
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());

            $('#search').click(Form.searchClick);
            Form.drawDataTable();
        },

        searchClick(){
            if (!$('#start_date').val()) return alert('filter date required');
            const spinner = '<tr><td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td></tr>'
            $('#agingTbl').DataTable().destroy();
            $('#agingTbl tbody').html(spinner);
            $('#agingTbl tfoot').empty();
                                    
            $.get("{{route('biller.suppliers.get_supplier_aging_report')}}", {
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                is_aging_filter: 1,
            })
            .then(response => {
                $('#agingTbl tbody').empty();
                const agingColTotals = [0,0,0,0,0,0];
                
                // Append new rows to the table
                response.suppliers_data.forEach(function(data) {
                    let row = `<tr><td>${data.supplier}</td>`;
                    data.aging_cluster.forEach(function(v, i) {
                        row += `<td>${accounting.formatNumber(v)}</td>`;
                        agingColTotals[i] += accounting.unformat(v);
                    });
                    agingColTotals[5] += accounting.unformat(data.total_aging);
                    row += `<td>${accounting.formatNumber(data.total_aging)}</td></tr>`;
                    $('#agingTbl tbody').append(row);
                });

                // Update totals
                let totalsRow = `<tr><td><b>Total</b></td>`;
                agingColTotals.forEach(function(total) {
                    totalsRow += `<td><b>${accounting.formatNumber(total)}</b></td>`;
                });
                totalsRow += `</tr>`;
                $('#agingTbl tfoot').append(totalsRow);

                // Re-draw table
                Form.drawDataTable();
            })
            .fail((xhr,status, error) => {
                $('#agingTbl tbody').empty();
                console.log(error);
            });
        },

        drawDataTable(){
            $('#agingTbl').dataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
            });
        }
    };

    $(Form.init);
</script>

@endsection