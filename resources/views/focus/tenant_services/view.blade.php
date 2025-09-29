@extends ('core.layouts.app')

@section('title', 'Tenant Service Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tenant Service Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.tenant_services.partials.tenant-services-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <h5 class="ml-2 mb-2 font-weight-bold">Total Cost: <span class="total-cost">{{ numberFormat($tenant_service->total_cost) }}</span></h5>
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Package Name' => $tenant_service->name,
                                'Costing' => numberFormat($tenant_service->cost),
                                'Maintenance Fee' => numberFormat($tenant_service->maintenance_cost),
                                'Maintenance Term (Months)' => $tenant_service->maintenance_term,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>
                    <br>
                    @if ($tenant_service->items->count())
                        <table class="table table-bordered table-sm">
                            @php
                                $details = [];
                                if (count($tenant_service->items)) $details['Extras Term (Months)'] = $tenant_service->maintenance_term;
                                foreach ($tenant_service->items as $key => $item) {
                                    $package = $item->package_extra;
                                    if ($package) $details[$package->name] = numberFormat($item->extra_cost);
                                }
                            @endphp
                            @foreach ($details as $key => $val)
                                <tr>
                                    <th width="30%">{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
