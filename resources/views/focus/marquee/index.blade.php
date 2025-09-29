@extends ('core.layouts.app')

@section ('title', 'Super Admin Marquees')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Super Admin Marquees</h2>
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
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">

                            <div class="row mb-2">
                                <div class="col-9 col-lg-6">
                                    <label for="business" >Filter by Business</label>
                                    <select class="form-control box-size filter" id="business" name="business" data-placeholder="Filter by Business">

                                        <option value=""></option>
                                        @foreach ($businesses as $biz)
                                            <option value="{{ $biz->id }}">
                                                {{ $biz->cname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                </div>
                            </div>

                            <table id="marqueeTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Business</th>
                                        <th>Content</th>
                                        <th>Start</th>
                                        <th>End</th>
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

    const businessFilter = $('#business');

    businessFilter.select2({ allowClear: true });

    $('.filter').change(() => {
        $('#marqueeTable').DataTable().destroy();
        draw_data();
    })

    const clearFilters = $('#clearFilters');


    clearFilters.click(() => {

        businessFilter.val('').trigger('change');

        $('#marqueeTable').DataTable().destroy();
        draw_data();
    })



    function draw_data() {

        const tableLan = {@lang('datatable.strings')};

        var dataTable = $('#marqueeTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.marquee.index") }}',
                type: 'GET',
                data: {
                    businessFilter: businessFilter.val(),
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'business', name: 'business' },
                { data: 'content', name: 'content' },
                { data: 'start', name: 'start' },
                { data: 'end', name: 'end' },
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false
                }
            ],            order: [
                [0, "asc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection