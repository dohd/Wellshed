<!DOCTYPE html >

@include('tinymce.scripts')

@extends ('core.layouts.app')

@section ('title',  'Edit Stock Issuance Request')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h3 class="mb-0">Edit Stock Issuance Request</h3>
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
                                {{ Form::open(['route' => ['biller.stock-issuance-request.update', $sir->sir_number], 'method' => 'PUT', 'id' => 'update-stock-issuance-request']) }}
                                <div class="form-group">

                                    <div class="row mb-2">

                                        @csrf <!-- CSRF protection -->

                                        <div class="col-4">
                                            <label for="requested_by">Requested by:</label>
                                            <select class="form-control box-size select2" id="employeeList" name="requested_by" required>
                                                <option value="">-- Select Employee --</option>
                                                @foreach ($employees as $emp)
                                                    <option value="{{ $emp['id'] }}" @if($sir->requested_by === $emp['id']) selected @endif>
                                                        {{ $emp['employee_name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-6 col-sm-8">
                                            <div class="form-group">
                                                <label for="project" class="caption">Project</label>
                                                <select class="form-control" name="project" id="project" data-placeholder="Search Project by Name, Customer, Branch">
                                                </select>
                                            </div>
                                        </div>



                                        <div class="col-12 col-lg-9 mt-1 mt-lg-1">
                                            <label for="notes">Notes:</label>
                                            <textarea id="notes" name="notes" class="form-control box-size mb-2" rows="4" >{{$sir['notes']}}</textarea>
                                        </div>

                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <h4>Saved Products</h4>
                                            <table class="table" id="saved-products-table">
                                                <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Code</th>
                                                    <th>Category</th>
                                                    <th>Warehouse</th>
                                                    <th>Quantity</th>
                                                    <th>Action</th> <!-- New column for Remove button -->
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
                                                        <td>
                                                            <!-- Display and edit the quantity using a text input -->
                                                            <input type="text" class="form-control saved-product-quantity" value="{{ $item['quantity'] }}">
                                                        </td>
                                                        <td>
                                                            <!-- Add a Remove button with a data-id attribute -->
                                                            <button class="btn btn-danger remove-saved-product-btn" data-id="{{ $item['siri_number'] }}">Remove</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <h4 class="mt-1">New Products</h4>
                                    <div class="row mb-2 mt-1">

                                        <div class="col-sm-10 col-lg-6">
                                            <label for="product">Product</label>
                                            <select class="form-control select2" id="product" data-placeholder="Search for Product" >
                                                <option value=""></option>
                                                @foreach($products as $prod)
                                                    <option value="{{ json_encode($prod) }}">
                                                        {{ $prod['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-10 col-lg-1">

                                            <label for="item_quantity"> Quantity </label>
                                            <input type="number" id="item_quantity" class="form-control box-size">
                                        </div>

                                        <input type="hidden" name="products_list" id="products_list_input">

                                        <div class="col-sm-2 col-lg-2 d-flex flex-column">
                                            <button type="button" id="add-item-btn" class="btn btn-secondary btn-md mt-auto">Add Item</button>
                                        </div>

                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th style="display: none;">Product ID</th>
                                                    <th>Name</th>
                                                    <th>Code</th>
                                                    <th>Category</th>
                                                    <th>Warehouse</th>
                                                    <th>Quantity</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tbody id="item-table-body">
                                                {{--            <!-- "Added Products will appear here" message -->--}}
                                                {{--            <tr id="empty-table-message">--}}
                                                {{--                <td colspan="7" class="text-center">Added Products will appear here.</td>--}}
                                                {{--            </tr>--}}
                                                </tbody>

                                            </table>

                                        </div>
                                        <p id="empty-table-message" class="col-md-11 text-center">Added Products will appear here.</p>
                                    </div>

                                    <div class="edit-form-btn">
                                        {{ link_to_route('biller.employee-daily-log.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-2']) }}
                                        {{ Form::submit('Save', ['class' => 'btn btn-primary btn-md', 'id' => 'submit-btn']) }}
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
@endsection
@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}

    <script>

        $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

        tinymce.init({
            selector: '#notes',
            menubar: 'file edit view format table tools',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 230,
        });


        const config = {
            select2: {allowClear: false},
        }

        $('#employeeList').select2(config.select2);
        $("#product").select2(config.select2);


        function select2Config(url, callback) {
            return {
                ajax: {
                    url,
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: ({term}) => ({q: term, keyword: term}),
                    processResults: callback
                }
            }
        }

        // load projects dropdown
        const projectUrl = "{{ route('biller.projects.project_search') }}";
        function projectData(data) {

            return {results: data.map(v => ({id: v.id, text: v.name}))};
        }
        $("#project").select2(select2Config(projectUrl, projectData));


        $(document).ready(function () {

            // project
            @php
                $project_name = '';
                $project = $sir->project;
                if ($project) {
                    $sirProject = \App\Models\project\Project::find($project);
                }
            @endphp
            const projectName = "{{ $sirProject->name }}";
            const projectId = "{{ $sirProject->id }}";
            $('#project').append(new Option(projectName, projectId, true, true)).change();



            /** Adding mew Products */
            $('#add-item-btn').click(function () {
                let selectedItem = JSON.parse($('#product').val());
                let itemQuantity = $('#item_quantity').val();

                // Check if both product and quantity inputs are not empty
                if (selectedItem && itemQuantity && itemQuantity.trim() !== "") {
                    // Check if the product with the same ID is already in the table
                    let existingItem = $('#item-table-body').find('td:first-child:contains(' + selectedItem.id + ')').closest('tr');

                    if (existingItem.length > 0) {
                        // If the product already exists, update the quantity input
                        let quantityInput = existingItem.find('td:eq(5) input');
                        let existingQuantity = parseInt(quantityInput.val());
                        let newQuantity = existingQuantity + parseInt(itemQuantity);

                        quantityInput.val(newQuantity);
                    } else {
                        // If the product doesn't exist, add a new row with an editable quantity input
                        $('#item-table-body').append(
                            '<tr>' +
                            '<td style="display: none;">' + selectedItem.id + '</td>' +
                            '<td>' + selectedItem.name + '</td>' +
                            '<td>' + selectedItem.code + '</td>' +
                            '<td>' + selectedItem.category + '</td>' +
                            '<td>' + selectedItem.warehouse + '</td>' +
                            '<td><input type="number" class="form-control quantity-input" value="' + itemQuantity + '"></td>' +
                            '<td><button class="remove-item-btn btn btn-danger btn-md mr-2">Remove</button></td>' +
                            '</tr>'
                        );
                    }

                    // Update the hidden input with the tabulated data
                    updateTabulatedDataInput();

                    // Clear the selection using select2 method
                    $('#product').val(null).trigger('change');
                    $('#item_quantity').val('');

                    // Hide the "Added Products will appear here" message
                    $('#empty-table-message').hide();
                } else {
                    // Display an alert or message indicating that both inputs are required
                    alert('Please select a product and enter a quantity.');
                }
            });

            // Event delegation for removing items dynamically
            $('#item-table-body').on('click', '.remove-item-btn', function () {
                // Check if the table is about to become empty
                let isTableBecomingEmpty = ($('#item-table-body tr').length - 1) === 0;

                $(this).closest('tr').remove();
                updateTabulatedDataInput(); // Update the hidden input after removal

                // Show the "Added Products will appear here" message if the table becomes empty
                if (isTableBecomingEmpty) {
                    $('#empty-table-message').show();
                }
            });

            // Event listener for input change to update the hidden input
            $('#item-table-body').on('change', '.quantity-input', function () {
                updateTabulatedDataInput();
            });

            // Function to update the hidden input with the tabulated data
            function updateTabulatedDataInput() {
                let productsList = [];

                $('#item-table-body tr').each(function () {
                    // Check if the row contains the message, and exclude it
                    if ($(this).find('td:eq(0)').text() !== "") {
                        let rowData = {
                            id: $(this).find('td:eq(0)').text(),
                            name: $(this).find('td:eq(1)').text(),
                            code: $(this).find('td:eq(2)').text(),
                            category: $(this).find('td:eq(3)').text(),
                            warehouse: $(this).find('td:eq(4)').text(),
                            quantity: $(this).find('td:eq(5) input').val(), // Get value from the input
                        };

                        productsList.push(rowData);
                    }
                });

                // Update the hidden input with the JSON representation of productsList
                $('#products_list_input').val(JSON.stringify(productsList));
            }



            //Managing already saved items
            $('#saved-products-table').on('click', '.remove-saved-product-btn', function () {
                // Remove the corresponding row from the table
                $(this).closest('tr').remove();
            });

            // Event handler for form submission
            $('#submit-btn').click(function () {
                // Get the saved products data from the table
                let sirItems = [];
                $('#saved-products-table tbody tr').each(function () {
                    let item = {
                        siri_number: $(this).find('td:eq(0)').text(),
                        name: $(this).find('td:eq(1)').text(),
                        code: $(this).find('td:eq(2)').text(),
                        category: $(this).find('td:eq(3)').text(),
                        warehouse: $(this).find('td:eq(4)').text(),
                        quantity: $(this).find('.saved-product-quantity').val()
                    };
                    sirItems.push(item);
                });

                // Convert the array to a JSON string
                let sirItemsJson = JSON.stringify(sirItems);

                // Append the JSON string as a hidden input to the form
                $('<input>').attr({
                    type: 'hidden',
                    name: 'sir_items',
                    value: sirItemsJson
                }).appendTo('form');
            });
        });






    </script>
@endsection