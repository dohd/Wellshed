<table id="prospect_prospect_table" class="table p-1 table-striped table-bordered prospectTable">
    <thead >
        <tr>
            <td>COMPANY</td>
            <td>PROSPECT NAME</td>
            <td>PHONE</td>
            <td>INDUSTRY</td>
            <td>CALL STATUS</td>
        </tr>
    </thead>
    
    <tbody>
        @if ($prospect)
               
                    <tr>
                        <td> {{ $prospect->company }} </td>
                        <td> {{ $prospect->contact_person }} </td>
                        <td> {{ $prospect->phone }} </td>
                        <td> {{ $prospect->industry }} </td>
                        <td> {{ $prospect->call_status }} </td>
                    </tr>
               
            @else
                <tr>
                    <td style="text-align: center; vertical-align: middle;" colspan="5"> No record found </td>
                </tr>
            @endif
       
       
    </tbody>
</table>  