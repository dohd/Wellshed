@extends ('core.layouts.app')

@section('title', 'Refill Customer Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Refill Customer Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.refill_customers.partials.refill-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    @php
                        $details = [
                            'Customer Name' => $refill_customer->name,
                            'Phone' => $refill_customer->phone,
                            'Email' => $refill_customer->email,
                            'Address' => $refill_customer->address,
                        ];
                    @endphp
                    @foreach ($details as $key => $value)
                        <div class="row">
                            <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                <p>{{ $key }}</p>
                            </div>
                            <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                <p>{{ $value }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
