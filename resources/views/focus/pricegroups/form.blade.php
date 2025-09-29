<div class="form-group row">
    <div class="col-6">
        <label for="client">Client</label>
        <select name="ref_id" id="client" class="form-control" data-placeholder="Choose client" required></select>
        <input type="hidden" name="is_client" value="1" id="is_client">
    </div>
    <div class="col-6">
        <label for="supplier">Supplier</label>
        <select name="ref_id" id="supplier" class="form-control" data-placeholder="Choose supplier" required></select>
    </div>
</div>
<div class='form-group row'>
    <div class="col-6">
        <label for="group_name">Price Group Name</label>
        {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Price Group Name', 'required']) }}
    </div>
    <div class="col-6">
        <label for="description">Description</label>
        {{ Form::text('description', null, ['class' => 'form-control', 'placeholder' => 'Description ']) }}
    </div>
</div>

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    const select2Config = (url, callback) => ({
        ajax: {
            url,
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({ search: term }),
            processResults: data  => callback(data),
        }
    });

    // fetch customers
    const clientUrl = "{{ route('biller.customers.select') }}";
    function clientCb(data) {
        return {
            results: data.map(v => ({ 
                id: v.id, 
                text: `${v.name} - ${v.company}`,
            }))
        }
    };
    $("#client").select2(select2Config(clientUrl, clientCb));

    // fetch suppliers
    const suppliertUrl = "{{ route('biller.suppliers.select') }}";
    function supplierCb(data) {
        return {
            results: data.map(v => ({ 
                id: v.id, 
                text: v.name + ' - ' + v.email 
            }))
        }
    };
    $("#supplier").select2(select2Config(suppliertUrl, supplierCb));

    // select constraint
    $('form').on('change', '#client, #supplier', function() {
        if ($(this).is('#client')) $('#supplier').attr('disabled', true);
        if ($(this).is('#supplier')) {
            $('#client').attr('disabled', true);
            $('#is_client').attr('disabled', true);
        }
    });
</script>
@endsection