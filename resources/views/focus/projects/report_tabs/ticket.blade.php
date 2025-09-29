<div class="tab-pane  active in" id="tab_data1" aria-labelledby="tab1" role="tabpanel">
    <div class="card">
        <div class="card-body">
            <table id="ticktsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tricket No.</th>
                        <th>Customer - Branch</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Source</th>
                        <th>Created At</th>
                        <th>Client Ref</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leads as $i => $lead)
                        <tr>
                            @php
                                 $client_name = $lead->client_name;
                                if ($lead->customer) $client_name = $lead->customer->company;
                                if ($client_name && $lead->branch) $client_name .= " - {$lead->branch->name}";
                                $source = $lead->LeadSource ? $lead->LeadSource->name : '';
                                if ($lead->status) {
                                    $lead->status =  '<div class="round" style="padding: 8px; color: white; background-color: #16D39A"> Closed </div>';
                                } else {
                                    $lead->status =  '<div class="round" style="padding: 8px; color: white; background-color: #00B5B8"> Open </div>';
                                }
                            @endphp
                            <td>{{$i+1}}</td>
                            <td>{{gen4tid("Tkt-", $lead->reference)}}</td>
                            <td>{{$client_name}}</td>
                            <td>{{$lead->title}}</td>
                            <td>{!!$lead->status !!}</td>
                            <td>{{$source}}</td>
                            <td>{{$lead->created_at}}</td>
                            <td>{{$lead->client_ref}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
</div>
