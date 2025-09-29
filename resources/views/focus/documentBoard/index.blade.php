@extends ('core.layouts.app')

@section ('title', 'Company Notice Board')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h2 class=" mb-0">Company Notice Board</h2>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.documentBoard.header-buttons')
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
                                <table id="company-notice-board-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Caption</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>
        setTimeout(() => draw_data(), "{{ config('master.delay') }}");

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

        function draw_data() {
            const tableLan = {@lang('datatable.strings')};
            try {
                var dataTable = $('#company-notice-board-table').DataTable({
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














{{--@extends('core.layouts.app')--}}

{{--@section('content')--}}
{{--    <div class="container">--}}
{{--        <h1>Company Notice Board</h1>--}}
{{--        <a href="{{ route('biller.documentboard.create') }}" class="btn btn-primary mb-3">Upload New Document</a>--}}

{{--        @if (session('success'))--}}
{{--            <div class="alert alert-success">--}}
{{--                {{ session('success') }}--}}
{{--            </div>--}}
{{--        @endif--}}

{{--        <table class="table table-bordered">--}}
{{--            <thead>--}}
{{--            <tr>--}}
{{--                <th>Caption</th>--}}
{{--                <th>Actions</th>--}}
{{--            </tr>--}}
{{--            </thead>--}}
{{--            <tbody>--}}
{{--            @foreach ($documents as $document)--}}
{{--                <tr>--}}
{{--                    <td>{{ $document->caption }}</td>--}}
{{--                    <td>--}}
{{--                        <a href="{{ route('biller.documentboard.view', $document->id) }}" class="btn btn-primary" target="_blank">View</a>--}}
{{--                        <a href="{{ route('biller.documentboard.download', $document->id) }}" class="btn btn-success">Download</a>--}}

{{--                        <form action="{{ route('biller.documentboard.destroy', $document->id) }}" method="POST" style="display:inline-block;">--}}
{{--                            @csrf--}}
{{--                            @method('DELETE')--}}
{{--                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this document?')">Delete</button>--}}
{{--                        </form>--}}
{{--                    </td>--}}
{{--                </tr>--}}
{{--            @endforeach--}}
{{--            </tbody>--}}
{{--        </table>--}}
{{--    </div>--}}
{{--@endsection--}}
