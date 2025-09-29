<!DOCTYPE html>

@extends ('core.layouts.app')

@section ('title', 'Edit Employee Notice')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Edit Employee Notice</h3>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.employeeNotice.partials.header-buttons')
                </div>
            </div>
        </div>

    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">

                            @include("focus.employeeNotice.form")

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('after-scripts')

    {{ Html::script('focus/js/select2.min.js') }}

    <script>



    </script>

@endsection

<style>
    .radius-8 {
        border-radius: 8px;
    }
</style>