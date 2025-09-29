@extends ('core.layouts.app')

@section('title', 'Refill Service Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Refill Service Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.product_refills.partials.refill-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                        @php
                            $details = [
                                'Service No.' => $product_refill->tid,
                                'Customer Name' => @$product_refill->product_customer->name,
                                'Service Date' => dateFormat($product_refill->date),
                                'Next Service Date' => dateFormat($product_refill->next_date),
                                'Reminder Start Date' => dateFormat($product_refill->rem_start_date),
                                'Reminder Interval' => $product_refill->rem_interval,
                                'Reminder Frequency' => $product_refill->rem_frequency,
                                'Note' => $product_refill->note,
                                'Service Products' => implode(', ', $product_refill->refill_products->pluck('name')->toArray()),
                            ];
                        @endphp
                        @foreach ($details as $key => $value)
                            <div class="row">
                                <div class="col-3 border-blue-grey border-lighten-5">
                                    <p>{{ $key }}</p>
                                </div>
                                <div class="col border-blue-grey border-lighten-5 font-weight-bold">
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
