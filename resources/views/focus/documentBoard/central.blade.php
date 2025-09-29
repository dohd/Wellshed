<!DOCTYPE html>

@include('tinymce.scripts')

@extends ('core.layouts.app')

@section ('title', 'Company Notice Board')

@section('content')
    <div class="content-wrapper">


        <ul class="nav nav-tabs" role="tablist">

            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1"
                   role="tab" aria-selected="true"><h4> Welcome Message </h4>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2"
                   role="tab" aria-selected="false"><h4> Company Notice </h4>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3"
                   role="tab" aria-selected="false"><h4> Document Board </h4>
                </a>
            </li>

        </ul>
        <div class="tab-content px-1 pt-1">

            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                @include('focus.documentBoard.welcome')
            </div>

            <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                @include('focus.documentBoard.notice')
            </div>

            <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                @include('focus.documentBoard.documentBoard')
            </div>

        </div>


    </div>
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>

        tinymce.init({
            selector: '.tiny-display',
            menubar: '',
            plugins: '',
            toolbar: '',
            height: 1000,
            readonly  : true,
        });

        setTimeout(() => draw_data(), "{{ config('master.delay') }}");

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

        function draw_data() {
            const tableLan = {@lang('datatable.strings')};
            try {
                var dataTable = $('#document-board-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    language: tableLan,
                    ajax: {
                        url: '{{ route("biller.company-notice-board.index") }}',
                        type: 'GET',
                    },
                    columns: [
                        {
                            data: 'caption',
                            name: 'caption'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [0, "asc"]
                    ],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print'],
                });
            } catch (error) {
                console.error('An error occurred while initializing DataTable:', error);
            }
        }


    </script>
@endsection
