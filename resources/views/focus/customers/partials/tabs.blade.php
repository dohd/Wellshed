<ul class="nav nav-tabs nav-top-border no-hover-bg " role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Customer Info</a>
    </li>
</ul>
<div class="tab-content px-1 pt-1">
    <!-- Customer Info -->
    <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                        @php
                            $labels = [
                                'Company', 'Phone', 'Email', 'Address',
                            ];
                        @endphp
                        <tbody>
                            @foreach ($labels as $key => $val)
                                <tr>
                                    <th>{{ is_numeric($key) ? $val : $key }}</th>
                                    <td>{{ $customer[strtolower($val)] }}</td>
                                </tr>
                            @endforeach      
                        </tbody>
                    </table>            
                </div>
            </div>
        </div>
    </div>
    <!-- End Customer Info -->
                
    <!-- Statement on Account -->
    <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
        <div class="row mt-2 mb-2">
            <div class="col-12 col-md-3">
                <select id="project" class="custom-select" data-placeholder="Search Project">
                    <option value=""></option>
                    @foreach ($projects as $row)
                        <option value="{{ $row->id }}">{{ gen4tid('PRJ-', $row->tid) }} {{ $row->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-8 col-md-3 mb-1">Search Date Between</div>
            <div class="col-8 col-md-2 mb-1">
                <input type="text" class="form-control form-control-sm datepicker start_date">
            </div>
            <div class="col-8 col-md-2 mb-1">
                <input type="text" class="form-control form-control-sm datepicker end_date" disabled>
            </div>
            <div class="col-12 col-md-3 mb-1">
                <input type="button" id="search2" value="Search" class="btn btn-info btn-sm search">
                <button type="button" id="refresh2" class="btn btn-success btn-sm mr-1 refresh"><i class="fa fa-refresh" aria-hidden="true"></i></button>
                <a href="{{ route('biller.customers.print_statement', [$customer, token_validator('', 's', true), 1]) }}" target="_blank"  class="btn btn-sm btn-purple print-on-account">
                    <i class="fa fa-print"></i> statement
                </a>
            </div>
        </div>
        <hr>
        <div class="table-responsive">
            <table id="transTbl" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>                                            
                        @foreach (['#', 'Date', 'Type', 'Note', 'Invoice Amount', 'Amount Paid', 'Account Balance'] as $val)
                            <th>{{ $val }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody></tbody>
            </table>            
        </div>

        <!-- Aging -->
        <div class="mt-2 aging">
            <h5>Aging (days)</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                    <thead>
                        <tr>                                                    
                            @foreach (['0 - 30', '31 - 60', '61 - 90', '91 - 120', '120+'] as $val)
                                <th>{{ $val }}</th>
                            @endforeach
                            <th>Aging Total</th>  
                            <th>Unallocated</th>
                            <th>Outstanding</th>                     
                        </tr>
                    </thead>
                    <tbody>
                        <tr>              
                            @php
                                $total_aging = 0;
                            @endphp          
                            @for ($i = 0; $i < count($aging_cluster); $i++) 
                                <td>
                                    {{ numberFormat($aging_cluster[$i]) }}
                                    @php
                                        $total_aging += $aging_cluster[$i];
                                    @endphp
                                </td>
                            @endfor
                            <td>{{ numberFormat($total_aging) }}</td>
                            <td>{{ numberFormat($customer->on_account) }}</td>
                            <td>{{ numberFormat($total_aging) }}</td>
                        </tr>                    
                    </tbody>                     
                </table>  
            </div>            
        </div>
    </div>
    <!-- End Statement on Account -->

    <!-- Invoices -->
    <div class="tab-pane" id="active3" aria-labelledby="link-tab3" role="tabpanel">
        <div class="table-responsive">
            <table id="invoiceTbl" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>                                                    
                        @foreach (['#', 'Date', 'Status', 'Note', 'Amount', 'Paid'] as $val)
                            <th>{{ $val }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody></tbody> 
            </table>            
        </div>
    </div>
    <!-- End Transactions -->

    <!-- Statement on Invoice  -->
    <div class="tab-pane" id="active4" aria-labelledby="link-tab4" role="tabpanel">
        <div class="row mt-2 mb-2">
            <div class="col-12 col-md-3">
                <select id="project2" class="custom-select" data-placeholder="Search Project">
                    <option value=""></option>
                    @foreach ($projects as $row)
                        <option value="{{ $row->id }}">{{ gen4tid('PRJ-', $row->tid) }} {{ $row->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-8 col-md-3 mb-1">Search Date Between</div>
            <div class="col-8 col-md-2 mb-1">
                <input type="text" class="form-control form-control-sm datepicker start_date">
            </div>
            <div class="col-8 col-md-2 mb-1">
                <input type="text" id="end_date" class="form-control form-control-sm datepicker end_date" disabled>
            </div>
            <div class="col-12 col-md-3 mb-1">
                <input type="button" id="search4" value="Search" class="btn btn-info btn-sm search">
                <button type="button" id="refresh4" class="btn btn-success btn-sm mr-1 refresh"><i class="fa fa-refresh" aria-hidden="true"></i></button>
                <a href="{{ route('biller.customers.print_statement', [$customer, token_validator('', 's', true), 2]) }}" target="_blank"  class="btn btn-sm btn-purple print-on-invoice">
                    <i class="fa fa-print"></i> statement
                </a>
            </div>
        </div>
        <hr>
        <div class="table-responsive">
            <table id="stmentTbl" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>                                            
                        @foreach (['#', 'Date', 'Type', 'Note', 'Invoice Amount', 'Amount Paid', 'Invoice Balance'] as $val)
                            <th>{{ $val }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody></tbody>  
            </table>            
        </div>
        <!-- Aging -->
    </div>
    <!-- End Transactions -->

    <!-- Aging -->
    <div class="tab-pane" id="active5" aria-labelledby="link-tab5" role="tabpanel"></div>    
    <!-- End Aging -->
</div>