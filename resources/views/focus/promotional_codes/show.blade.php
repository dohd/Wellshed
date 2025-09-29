<!DOCTYPE html>

@extends ('core.layouts.app')

@include('tinymce.scripts')

@section('title', 'View Promotional Code')

@section('page-header')
    <h1>
        View Promotional Code
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0"> View Promotional Code </h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.promotional_codes.header-buttons')
                        </div>

                    </div>
                </div>
            </div>
            <div class="content-body mt-1">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header">
                                <!-- Assuming $record is passed to the view -->

                                @if ($promotionalCode->online_status === 'published')
                                    <!-- Show Unpublish button -->
                                    <form action="{{ route('biller.promotions.update-status', $promotionalCode->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="online_status" value="unpublished">
                                        <input type="hidden" name="published_at" value="">
                                        <button type="submit"
                                            class="px-4 py-2 text-white rounded-lg btn-primary">
                                            Unpublish
                                        </button>
                                    </form>
                                @else
                                    <!-- Show Publish button -->
                                    <form action="{{ route('biller.promotions.update-status', $promotionalCode->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="online_status" value="published">
                                        <input type="hidden" name="published_at" value="{{ now() }}">
                                        <button type="submit"
                                            class="px-4 py-2 text-white rounded-lg btn-success">
                                            Publish
                                        </button>
                                    </form>
                                @endif

                            </div>
                            <div class="card-content">

                                <div class="card-body">


                                    <div class="form-group">

                                        {{-- Including Form blade file --}}
                                        @include('focus.promotional_codes.view_form')

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
