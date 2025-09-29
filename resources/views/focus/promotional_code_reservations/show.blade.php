<!DOCTYPE html>

@extends ('core.layouts.app')

@include('tinymce.scripts')

@section ('title', 'Promotional Code Reservation')

@section('page-header')
    <h1>
        Promotional Code Reservation
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0"> Promotional Code Reservation </h4>

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


                                    <div class="form-group">


{{--                                        @if(@$refererName)--}}
{{--                                            <p style="margin-bottom: 30px; font-size: 18px;"><i><span style="font-size: 15px;">This recipient was referred by:</span> {{ @$refererName }}</i></p>--}}

{{--                                        @endif--}}


{{--                                        @if(!empty(@$refererChain) && $logged_in_user)--}}

{{--                                            <div class="mb-3">--}}

{{--                                                <p>Here's a list of referrers that came before, arranged from most recent to earliest: </p>--}}

{{--                                                @foreach(@$refererChain as $index => $parent)--}}
{{--                                                    <span>--}}
{{--                                                        <b>{{ $index + 1 }}.)</b> <i><span style="font-size: 15px;"></span> {{ $parent }}</i>--}}
{{--                                                    </span>--}}
{{--                                                    <br>--}}
{{--                                                @endforeach--}}

{{--                                            </div>--}}

{{--                                        @endif--}}

                                            {{-- Including Form blade file --}}
                                        @include('focus.promotional_code_reservations.form')

                                    </div>


                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

