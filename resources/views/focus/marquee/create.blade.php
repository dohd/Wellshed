@php use App\Models\marquee\UserMarquee; @endphp
        <!DOCTYPE html>

@extends ('core.layouts.app')


@section ('title',  'Set Marquee Content')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h3 class="mb-0">Set Marquee Content</h3>
            </div>

            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.marquee.header-buttons')
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
                                {{ Form::open(['route' => 'biller.marquee.store', 'method' => 'POST', 'id' => 'create-marquee']) }}
                                <div class="form-group">
                                    {{-- Including Form blade file --}}
                                    @include("focus.marquee.form")
                                    <div class="edit-form-btn mt-2">
                                        {{ link_to_route('biller.dashboard', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md']) }}
                                        {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                        <div class="clearfix"></div>
                                    </div>

                                    @php
                                        $now = (new DateTime())->format('Y-m-d H:i:s');

                                        $userMarquee = UserMarquee::where('start', '<=', $now)->where('end', '>=', $now)->first() ?
                                                UserMarquee::where('start', '<=', $now)->where('end', '>=', $now)->first() :
                                                "";
                                    @endphp

                                    @if($userMarquee && \Illuminate\Support\Facades\Auth::user()->ins !== 2)
                                        <a href="{{ route('biller.delete-user-marquee', $userMarquee->id) }}"
                                           class="btn btn-danger mt-3">
                                            <i class="fa fa-trash"></i> Delete Current Marquee Message
                                        </a>
                                    @endif
                                </div>
                                {{ Form::close() }}

                                @if(\Illuminate\Support\Facades\Auth::user()->business->is_main)

                                    <h2 class="mt-4"> Old Admin Marquee Messages </h2>
                                    <table id="oldSuperAdminMarqueesTable"
                                           class="table table-striped table-bordered zero-configuration"
                                           cellspacing="0"
                                           width="100%">
                                        <thead>
                                            <tr>
                                                <th>Business</th>
                                                <th>Content</th>
                                                <th>Start</th>
                                                <th>End</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>

                                @else

                                    <h2 class="mt-4"> Old Marquee Messages </h2>
                                    <table id="oldUserMarqueesTable"
                                           class="table table-striped table-bordered zero-configuration"
                                           cellspacing="0"
                                           width="100%">
                                        <thead>
                                            <tr>
                                                <th>Content</th>
                                                <th>Start</th>
                                                <th>End</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>

                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-scripts')

    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>

        const isAdmin = {{ \Illuminate\Support\Facades\Auth::user()->business->is_main }};

        if (isAdmin) setTimeout(() => drawSuperAdminMarqueesTables(), "{{ config('master.delay') }}");
        else setTimeout(() => drawUserMarqueesTables(), "{{ config('master.delay') }}");



        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

        $(document).ready(function () {

            $('#business').select2({allowClear: true});
        });

        function drawSuperAdminMarqueesTables() {

            $('#oldSuperAdminMarqueesTable').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.old-admin-marquees") }}',
                    type: 'get',
                    data: {
                        //
                    }
                },
                columns: [
                    {data: 'business', name: 'business'},
                    {data: 'content', name: 'content'},
                    {data: 'start', name: 'start'},
                    {data: 'end', name: 'end'},
                ],
                order: [[0, 'desc']],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });


        }


        function drawUserMarqueesTables() {

            $('#oldUserMarqueesTable').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.old-user-marquees") }}',
                    type: 'get',
                    data: {
                        //
                    }
                },
                columns: [
                    {data: 'content', name: 'content'},
                    {data: 'start', name: 'start'},
                    {data: 'end', name: 'end'},
                ],
                order: [[0, 'desc']],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });


        }


    </script>
@endsection