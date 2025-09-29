<div class="tab-pane in" id="tab_data2" aria-labelledby="tab2" role="tabpanel">
    <div class="card">
        <div class="card-body">
            <table id="djcsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Report No.</th>
                        <th>Customer - Branch</th>
                        <th>Subject</th>
                        <th>JobCard</th>
                        <th>Client Ref</th>
                        <th>Ticket No</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    
                        @foreach ($djcs as $i => $djcItems)
                        @if (count($djcItems) > 0)
                        @foreach ($djcItems as $djc)
                        <tr>
                            @php
                                $link = '';
                                $client_name = '';
                                $lead_tid = '';
                                if ($djc->client) {
                                    $customer = $djc->client->company;
                                    if ($djc->branch) $customer .= " - {$djc->branch->name}";
                                    $client_name = $customer;
                                }
                                if ($djc->lead) {
                                    // $client_name = $djc->lead->client_name;
                                    $lead_tid = gen4tid("TkT-", $djc->lead->reference);
                                }
                                
                            @endphp
                            <td>{{$i+1}}</td>
                            <td>{{gen4tid("DJR-", $djc->tid)}}</td>
                            <td>{{$client_name}}</td>
                            <td>{{$djc->subject}}</td>
                            <td>{{$djc->job_card}}</td>
                            <td>{{$djc->client_ref}}</td>
                            <td>{{$lead_tid}}</td>
                            <td>{{$djc->created_at}}</td>
                        </tr>
                        @endforeach
                        
                            
                        @endif
                           
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
</div>
