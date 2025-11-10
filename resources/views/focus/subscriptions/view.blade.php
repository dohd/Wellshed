@extends ('core.layouts.app')
@section ('title', 'Subscription Managment')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>Subscription Managment</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.subscriptions.partials.header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="button-group">
                <a href="#" class="btn btn-info btn-sm mr-1" data-toggle="modal" data-target="#upgradeModal">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Upgrade Plan
                </a>   
            </div>
        </div>
        <div class="card-body">
            <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    @php
                        $sub = $subscription;
                        $status = ucfirst($sub->status);
                        if ($sub->status === 'active') {
                            $status = '<span class="badge bg-success">'.$status.'</span>';
                        } elseif ($sub->status === 'suspended') {
                            $status = '<span class="badge bg-warning">'.$status.'</span>';
                        } elseif ($sub->status === 'expired') {
                            $status = '<span class="badge bg-danger">'.$status.'</span>';
                        }

                        $renewalDate = $sub->last_renewal_date? Carbon::parse($sub->last_renewal_date)->format('M d, Y') : '';
                        $packageName = '';
                        $packagePrice = '';
                        if (@$sub->package) {
                            $packageName = $sub->package->name;
                            $packagePrice = numberFormat($sub->package->price);
                        }
                        $details = [
                            'Status' => $status,
                            'Code' => gen4tid('SUB-', $sub->tid),
                            'Name / Company' => @$sub->customer->name ?? @$sub->customer->company,
                            'Segment' => ucfirst(@$sub->customer->segment),
                            'Package' => $packageName? "{$packageName} (KES {$packagePrice} / month)" : '',
                            'Start Date' => \Carbon\Carbon::parse($sub->start_date)->format('M d, Y'),
                            'End Date' => \Carbon\Carbon::parse($sub->end_date)->format('M d, Y'),
                            'Last Renewal Date' => $renewalDate,
                        ];
                    @endphp
                    @foreach ($details as $key => $val)
                        <tr>
                            <th width="40%">{{ $key }}</th>
                            <td>{!! $val !!}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('focus.subscriptions.modals.upgrade_modal')
@endsection

@section('after-scripts')
<script>
    $.ajaxSetup({ 
      headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } 
    });

    // Success CB
    function trigger(data) {
        setTimeout(() => location.reload(), 1500);
    }
    // Error CB
    function errorTrigger(data) {
        if (data.message) alert(data.message);
        else alert('Something went wrong. Please try again later or contact admin');
    }

    // Submit Form
    $('#upgradeForm').on('submit', function(e) {
        e.preventDefault(); 
        e.stopPropagation();
        
        // Ajax 
        const payload = {
            form: $(this).serialize(), 
            url: "{{ route('biller.subscriptions.upgrade') }}",
        };
        addObject(payload, true);
        $('#upgradeModal').modal('hide');
    });
</script>
@endsection