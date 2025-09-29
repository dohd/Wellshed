<!DOCTYPE html>

@extends ('core.layouts.app')

@include('tinymce.scripts')

@section ('title', 'Edit Refer a friend or potential client to earn')

@section('page-header')
    <h1>
        Edit Refer a friend or potential client to earn
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0"> Edit Refer a friend or potential client to earn </h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.promotional_code_reservations.header-buttons')
                        </div>

                    </div>
                </div>
            </div>
            <div class="content-body mt-1">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">


                                    @if(@$isCustomer)
                                        {{ Form::open(['route' => ['biller.update-reserve-customer-promo-code', $reservation->uuid], 'method' => 'PUT', 'id' => 'create-employee-daily-log']) }}
                                    @else

                                        @if(@$isReferral)

                                            {{ Form::open(['route' => ['biller.update-reserve-referral-promo-code', $reservation->uuid], 'method' => 'PUT', 'id' => 'create-employee-daily-log']) }}
                                        @else

                                            {{ Form::open(['route' => ['biller.update-reserve-3p-promo-code', $reservation->uuid], 'method' => 'PUT', 'id' => 'create-employee-daily-log']) }}
                                        @endif

                                    @endif

                                    <div class="form-group">

                                        {{-- Including Form blade file --}}
                                        @include('focus.promotional_code_reservations.form')

                                        <div class="edit-form-btn mt-3">
                                            {{ link_to_route('biller.promotional-codes.index', 'Cancel', [], ['class' => 'btn btn-secondary btn-md mr-1']) }}
                                            {{ Form::submit('Update Reservation', ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div>

                                    </div>

                                    {{ Form::close() }}


                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

