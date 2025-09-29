<!DOCTYPE html >

@include('tinymce.scripts')

@extends ('core.layouts.app')

@section ('title',  'Edit Stock Issuance Request')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                @if(empty($sia))
                    <h3 class="mb-0">Stock Issuance Request</h3>
                @else
                    <h3 class="mb-0">Stock Issuance Approval</h3>
                @endif
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.stockIssuanceRequest.partials.header-buttons')
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
                                <div class="form-group">

                                    <div class="flex flex-row mb-2">

                                        <a href="{{ route('biller.stock-issuance-request.edit', $sir['sir_number']) }}" class="btn btn-warning mb-1"><i class="fa fa-pencil"></i> Edit</a>

                                    </div>


                                    @if(!empty($sia))
                                        <h4 class="mb-1">Approval</h4>
                                        <div class="row mb-2">

                                            <div class="col-4">
                                                <label for="requested_by">Approved by:</label>
                                                <input type="text" readonly value="{{ $sia['approved_by'] }}" class="form-control box-size mb-2">
                                            </div>

                                            <div class="col-4">
                                                <label for="date">Date:</label>
                                                <input id="date" type="text" readonly value="{{ (new DateTime($sia['date']))->format('jS F, Y') . ' at ' . (new DateTime($sia['date']))->format('h:i a') }}" class="form-control box-size mb-2">
                                            </div>

                                        </div>

                                        <hr>
                                    @endif

                                    <h4 class="mb-1">Stock Issuance Request</h4>
                                    <div class="row mb-2">

                                        @csrf <!-- CSRF protection -->

                                        <div class="col-4">
                                            <label for="requested_by">Requested by:</label>
                                            <input type="text" readonly value="{{ $sir['requested_by'] }}" class="form-control box-size mb-2">
                                        </div>

                                        <div class="col-4">
                                            <label for="date">Date:</label>
                                            <input id="date" type="text" readonly value="{{ (new DateTime($sir['date']))->format('jS F, Y') . ' at ' . (new DateTime($sir['date']))->format('h:i a') }}" class="form-control box-size mb-2">
                                        </div>


                                        <div class="col-6 col-sm-8">
                                            <div class="form-group">
                                                <label for="project" class="caption">Project</label>
                                                <textarea readonly id="project" name="project" class="form-control box-size mb-2" rows="4" >{{$sir['project']}}</textarea>
                                            </div>
                                        </div>


                                        <div class="col-12 col-lg-9 mt-1 mt-lg-1">
                                            <label for="notes">Notes:</label>
                                            <textarea id="notes" name="notes" class="form-control box-size mb-2" rows="4" >{{$sir['notes']}}</textarea>
                                        </div>

                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <h4>Products</h4>
                                            <table class="table" id="saved-products-table">
                                                <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Code</th>
                                                    <th>Category</th>
                                                    <th>Warehouse</th>
                                                    <th>Quantity</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <!-- Use a loop to generate rows based on the saved products JSON -->
                                                @foreach($sirItems as $item)
                                                    <tr>
                                                        <td style="display: none;">{{ $item['siri_number'] }}</td>
                                                        <td>{{ $item['name'] }}</td>
                                                        <td>{{ $item['code'] }}</td>
                                                        <td>{{ $item['category'] }}</td>
                                                        <td>{{ $item['warehouse'] }}</td>
                                                        <td>{{ $item['quantity'] }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>



                                    <div class="edit-form-btn">

                                        <div class="flex flex-row mt-2 mb-1">

                                            @if(empty($sia))
                                                <a href="{{ route('biller.sir-approve', $sir['sir_number']) }}" class="btn btn-blue-grey mb-1 ml-1"><i class="fa fa-ticket"></i> Approve </a>
                                            @endif

{{--                                            @else--}}
{{--                                                <a href="{{ route('biller.sir-reject', $sia['sia_number']) }}" class="btn btn-danger mb-1 ml-1"><i class="fa fa-xing"></i> Reject </a>--}}
{{--                                            @endif--}}

                                        </div>

                                        <div class="clearfix"></div>
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
@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}

    <script>

        $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});


        tinymce.init({
            selector: '#notes',
            menubar: '',
            plugins: '',
            toolbar: '',
            height: 340,
            readonly  : true,
            content_style: 'body { background-color: #ECEFF1; }',
        });

        tinymce.init({
            selector: '#project',
            menubar: '',
            plugins: '',
            toolbar: '',
            height: 30,
            readonly  : true,
            content_style: 'body { background-color: #ECEFF1; }',
        });


    </script>
@endsection