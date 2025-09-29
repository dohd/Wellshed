@include('tinymce.scripts')


<div class="row mb-2">

    @csrf <!-- CSRF protection -->

    <div class="col-4">
        <label for="requested_by">Requested by:</label>
        <select class="form-control box-size select2" id="employeeList" name="requested_by" required>
            <option value="">-- Select Employee --</option>
            @foreach ($employees as $emp)
                <option value="{{ $emp['id'] }}">
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

    <div class="col-sm-10 col-lg-4">
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


    <div class="col-12 col-lg-9 mt-1 mt-lg-1">
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes" class="form-control box-size mb-2" rows="4" ></textarea>
    </div>





</div>

<div class="row mt-4">
    <div class="col-md-11">
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


@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">

    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

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
                        '<td><button class="remove-item-btn btn btn-secondary btn-md mr-2">Remove</button></td>' +
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
    });


    tinymce.init({
            selector: '#notes',
            menubar: 'file edit view format table tools',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 230,
        });




</script>
@endsection
