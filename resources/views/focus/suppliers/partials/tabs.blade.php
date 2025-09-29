<ul class="nav nav-tabs nav-top-border no-hover-bg " role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Supplier Info</a>
    </li>
    <li class="nav-item">
        <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Statement on Account</a>
    </li>
    <li class="nav-item">
        <a class="nav-link " id="active-tab4" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">Bills</a>
    </li>   
    <li class="nav-item">
        <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Statement on Bill</a>
    </li>
    <li class="nav-item">
        <a class="nav-link " id="active-tab5" data-toggle="tab" href="#active5" aria-controls="active5" role="tab">Statement on Orders</a>
    </li>
</ul>
<div class="tab-content px-1 pt-1">
    <!-- Supplier info -->
    <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-bordered zero-configuration" cellspacing="0" width="100%">
                @php
                    $labels = [
                        'Name', 'Email', 'Address', 'City', 'Region', 'Country', 'PostBox', 'Bank',
                        'Supplier No' => 'supplier_no', 
                        'Tax ID' => 'taxid',  
                        'Account No' => 'account_no', 
                        'Account Name' => 'account_name', 
                        'Bank Code' =>  'bank_code',
                        'Mpesa Account' => 'mpesa_payment',
                        'Document ID' => 'docid',
                        'Contact Person Info' => 'contact_person_info'
                    ];
                @endphp
                <tbody>
                    <tr>
                        @php
                            $prefix = prefixesArray(['supplier'], auth()->user()->ins);
                        @endphp

                        <th>Supplier Number</th>
                        <td>{{ ($prefix ? $prefix[0] . '-' : 'SUPP-') . $supplier->id }}</td>
                    </tr>
                    @foreach ($labels as $key => $val)
                        <tr>
                            <th>{{ is_numeric($key) ? $val : $key }}</th>
                            <td>{{ $supplier[strtolower($val)] }}</td>
                        </tr>
                    @endforeach                        
                </tbody>
            </table>            
        </div>
    </div>

    <!-- Statement on account -->
    <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
        <div class="row mb-1">
            <div class="col-md-3">
                <select id="poSelect" class="custom-select" data-placeholder="Search Purchase Order">
                    <option value=""></option>                    
                </select>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-8 col-md-2 mb-1">Search Date Between</div>
            <div class="col-8 col-md-2 mb-1">
                <input type="text" class="form-control form-control-sm datepicker start_date">
            </div>
            <div class="col-8 col-md-2 mb-1">
                <input type="text" class="form-control form-control-sm datepicker end_date">
            </div>
            <div class="col-12 col-md-2">
                <input type="button" id="search2" value="Search" class="btn btn-info btn-sm search">
                <button type="button" id="refresh2" class="btn btn-success btn-sm refresh"><i class="fa fa-refresh" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="transTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>                                                        
                        @foreach (['#', 'Date', 'Type', 'Note', 'Bill Amount', 'Paid Amount', 'Account Balance'] as $val)
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
                            <td>{{ numberFormat($supplier->on_account) }}</td>
                            <td>{{ numberFormat($total_aging) }}</td>
                        </tr>                    
                    </tbody>                     
                </table>  
            </div>            
        </div>
    </div>

    <!-- Bills list -->
    <div class="tab-pane" id="active4" aria-labelledby="link-tab4" role="tabpanel">
        <div class="table-responsive">
            <table id="billTbl" class="table table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>                                          
                        @foreach (['#', 'Date', 'Status', 'Note', 'Bill Amount', 'Amount Paid'] as $val)
                            <th>{{ $val }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody></tbody>
            </table>            
        </div>
    </div>

    <!-- Statement on bill  -->
    <div class="tab-pane" id="active3" aria-labelledby="link-tab3" role="tabpanel">
        <div class="row mb-1">
            <div class="col-md-3">
                <select id="poSelect2" class="custom-select" data-placeholder="Search Purchase Order">
                    <option value=""></option>                    
                </select>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-8 col-md-2 mb-1">Search Date Between</div>
            <div class="col-8 col-md-2 mb-1">
                <input type="text" class="form-control form-control-sm datepicker start_date">
            </div>
            <div class="col-8 col-md-2 mb-1">
                <input type="text" id="end_date" class="form-control form-control-sm datepicker end_date">
            </div>
            <div class="col-12 col-md-2 mb-1">
                <input type="button" id="search4" value="Search" class="btn btn-info btn-sm search">
                <button type="button" id="refresh4" class="btn btn-success btn-sm refresh"><i class="fa fa-refresh" aria-hidden="true"></i></button>
            </div>
        </div>   
        <div class="table-responsive">
            <table id="stmentTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>                                                        
                        @foreach (['#', 'Date', 'Type', 'Note', 'Bill Amount', 'Paid Amount', 'Bill Balance'] as $val)
                            <th>{{ $val }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody></tbody> 
            </table>            
        </div>     
        <div class="stment-aging-wrapper"></div>
    </div>

    <!-- Statement on orders -->
    <div class="tab-pane" id="active5" aria-labelledby="link-tab5" role="tabpanel">
        <div class="col-8 col-md-2 mb-1">
            <button type="button" id="refresh5" class="btn btn-success btn-sm refresh">
                <i class="fa fa-refresh" aria-hidden="true"></i> Refresh
            </button>
        </div>
        <div class="table-responsive">
            <table id="grn-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Unit Of Measure</th>
                    <th>Quantity</th>
                    <th>Value</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
                </tr>
                </tbody>
            </table>            
        </div>
    </div>
</div>
