@include('tinymce.scripts')


{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">

    tinymce.init({
        selector: '.tinyinput',
        menubar: 'file edit view format table tools',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 300,
    });


    const config = {
        ajax: { 
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"}
        },
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
    };

    // djc attributes for edit mode
    const djc = @json(@$djc);

    // initialize html editor
    editor();
    // initialize date picker 
    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
    // ajax setup
    $.ajaxSetup(config.ajax);

    // lead select config
    $('#lead_id').select2({
        allowClear: true,
        placeholder: 'Search by No, Client, Branch, Title'
    });
    // lead select on create mode
    if (!djc) $('#lead_id').val('').trigger('change');

    $('#lead_id').change(function() {
        const opt = $(this).find('option:selected');
        $("#subject").val(opt.attr('title'));
        $("#client_id").val(opt.attr('client_id'));
        $("#branch_id").val(opt.attr('branch_id'));
        $("#client_ref").val(opt.attr('client_ref'));
    });
    // lead select on edit mode
    if (djc) $('#lead_id').trigger('change');

    // add jobcard value to default equipment row
    $("#jobcard").focusout(function() {
        $('#jobcard-0').val($(this).val());
    });
    
    // product row
    function productRow(n) {            
        return `
            <tr>
                <td><input type="text" class="form-control unique-id" name="unique_id[]" placeholder="Search Equipment" id="uniqueid-${n}"></td>
                <td><input type="text" class="form-control jobcard-row" name="jobcard[]" id="jobcard-${n}"></td>
                <td><input type="text" class="form-control equip-serial" name="equip_serial[]" id="equipserial-${n}"></td>
                <td><input type="text" class="form-control make-type" name="make_type[]" id="maketype-${n}"></td>
                <td><input type="text" class="form-control capacity" name="capacity[]" id="capacity-${n}"></td>
                <td><input type="text" class="form-control location" name="location[]" id="location-${n}"></td>
                <td><input type="text" class="form-control datepicker last-svc-date" name="last_service_date[]" id="lastservicedate-${n}"></td>
                <td><input type="text" class="form-control datepicker next-svc-date" name="next_service_date[]" id="nextservicedate-${n}"></td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item delete" href="javascript:" data-rowid="${n}" >Remove</a>
                            <a class="dropdown-item up" href="javascript:">Up</a>
                            <a class="dropdown-item down" href="javascript:">Down</a>
                        </div>
                    </div>
                </td>
                <input type="hidden" name="row_index[]" value="0" class="row-index" id="rowindex-${n}">
                <input type="hidden" name="item_id[]" value="0" class="item-id" id="itemid-${n}">
            </tr>
        `;
    }

    // equipment row counter;
    let rowId = 0;
    $('#equipmentsTbl tbody').append(productRow(0));
    $('#equipmentsTbl .datepicker').datepicker(config.date).datepicker('setDate', new Date());
    $('#uniqueid-0').autocomplete(autocompleteProp(0));
    
    // on clicking addproduct
    $('#addqproduct').on('click', function() {
        rowId++;
        const i = rowId;
        $('#equipmentsTbl tbody').append(productRow(i));
        $('#uniqueid-' + i).autocomplete(autocompleteProp(i));

        $('#jobcard-'+i).val($("#jobcard").val());  
        $('#lastservicedate-'+ i).datepicker(config.date).datepicker('setDate', new Date());
        $('#nextservicedate-'+ i).datepicker(config.date).datepicker('setDate', new Date());
        assignIndex();
    });

    // on clicking equipment drop down options
    $("#equipmentsTbl").on("click", ".up, .down, .delete", function() {
        var row = $(this).parents("tr:first");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.delete')) $(this).closest('tr').remove();
        assignIndex();
    });

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#client_id").val(),
                    dataType: "json",
                    method: 'post',
                    data: {
                        keyword: request.term, 
                        customer_id: $('#lead_id option:selected').attr('client_id'),
                        branch_id: $('#lead_id option:selected').attr('branch_id')
                    },
                    success: data => {
                        data = data.map(v => {
                            for (const key in v) {
                                if (!v[key]) v[key] = '';
                            }
                            const label = `${v.unique_id} ${v.equip_serial} ${v.make_type} ${v.model} ${v.machine_gas}
                                ${v.capacity} ${v.location} ${v.building} ${v.floor}`;
                            const value = v.unique_id;
                            const data = v;
                            return {label, value, data};
                        })
                        response(data);
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                // console.log(data)
                $('#uniqueid-'+i).val(data.unique_id);
                $('#equipserial-'+i).val(data.equip_serial);
                $('#maketype-'+i).val(data.make_type);
                $('#capacity-'+i).val(data.capacity);
                $('#location-'+i).val(data.location);

                const lastDate = data.last_maintenance_date? new Date(data.last_maintenance_date) : '';
                const nextDate = data.next_maintenance_date? new Date(data.next_maintenance_date) : '';
                $('#lastservicedate-'+i).datepicker('setDate', lastDate);
                $('#nextservicedate-'+i).datepicker('setDate', nextDate);
            }
        };
    }
    
    // assign row index
    function assignIndex() {
        $('#equipmentsTbl tr').each(function(i) {
            if (i > 0) $(this).find('.row-index').val(i);
        });
    }

    // check if file input has file and set caption validation to required
    const keys = ['one', 'two', 'three', 'four'];
    keys.forEach(v => {
        const imageId = '#image_'+v;
        const captionId = '#caption_'+v;
        $(imageId).on('change', function() {
            if ($(imageId).get(0).files.length > 0) {
                $(captionId).prop('required', true);
            } else {
                $(captionId).prop('required', false);
            }
        });
    });    

    // equipment line items on edit mode;
    const djcItems = @json(@$djc_items);
    if (djcItems) {
        $('#equipmentsTbl tbody tr').remove();
        djcItems.forEach((data,i) => {
            // console.log(data)
            i = i+1;
            $('#addqproduct').click();
            $('#itemid-'+i).val(data.id);
            $('#uniqueid-'+i).val(data.unique_id);
            $('#jobcard-'+i).val(data.jobcard);
            $('#equipserial-'+i).val(data.equip_serial);
            $('#maketype-'+i).val(data.make_type);
            $('#capacity-'+i).val(data.capacity);
            $('#location-'+i).val(data.location);

            const lastDate = data.last_service_date? new Date(data.last_service_date) : '';
            const nextDate = data.next_service_date? new Date(data.next_service_date) : '';
            $('#lastservicedate-'+i).datepicker('setDate', lastDate);
            $('#nextservicedate-'+i).datepicker('setDate', nextDate);
        });
    }
</script>