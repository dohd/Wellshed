@section('extra-scripts')
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    let tableRow = $('#itemTbl tbody tr:first').html();
    const stockUrl = "{{ route('biller.products.purchase_search') }}";
    $('#itemTbl tbody tr:first').remove();
    let rowIds = 1;
    $('.toolname').autocomplete(predict(stockUrl,stockSelect));
     $('#addtool').click(function() {
        rowIds++;
        let i = rowIds;
        const html = tableRow.replace(/-0/g, '-'+i);
        $('#itemTbl tbody').append('<tr>' + html + '</tr>');
        $('.toolname').autocomplete(predict(stockUrl,stockSelect));
    });

    $('#itemTbl').on('click', '.remove', removeRow);
    function removeRow() {
        const $tr = $(this).parents('tr:first');
        $tr.next().remove();
        $tr.remove();
    }
    let stockNameRowId = 0;
    function stockSelect(event, ui) {
        
        const {data} = ui.item;
        const i = stockNameRowId;
        $('#stockitemid-'+i).val(data.id);
        $('#toolname-'+i).val(data.name);
        $('#quantity-'+i).val(data.qty);
        $('#qty-'+i).val(data.qty);
        $('#code-'+i).val(data.code);
        $('#cost-'+i).val(data.purchase_price)
        $('#item_id-'+i).val(data.id);
        $('#uom-'+i).html('');
        if(data.units)
        data.units.forEach(v => {
            const option = `<option value="${v.code}" >${v.code}</option>`;
            $('#uom-'+i).append(option);
        });
        if(data.uom){
            const option = `<option value="${data.uom}"  >${data.uom}</option>`;
        $('#uom-'+i).append(option);
        }
       
    }
    $('#itemTbl').on('mouseup', '.toolname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.toolname')) stockNameRowId = id;
    }); 

    function predict(url, callback) {
        return {
            source: function(request, response) {
                $.ajax({
                    url,
                    dataType: "json",
                    method: "POST",
                    data: {keyword: request.term, pricegroup_id: $('#pricegroup_id').val()},
                    success: function(data) {
                        response(data.map(v => ({
                            label: v.name,
                            value: v.name,
                            data: v
                        })));
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: callback
        };
    }
</script>
@endsection