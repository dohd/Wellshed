<script>  
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    // customer select2
    $('#customer').select2({
        allowClear: true,
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({search: term}),
            processResults: result => {
                return { results: result.map(v => ({text: `${v.company}`, id: v.id }))};
            }      
        },
    }).change(function() {
        if (!$(this).val()) return;
        // fetch branches
        $("#branch").html('').select2({
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term, customer_id: $(this).val()}),                                
                processResults: data => {
                    data = data.filter(v => v.name != 'All Branches');
                    return { results: data.map(v => ({ text: v.name, id: v.id })) };
                },
            }
        });
        // fetch customer contracts
        $("#contract").html('').select2({
            allowClear: true,
            ajax: {
                url: "{{ route('biller.contracts.customer_contracts')  }}",
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term, customer_id: $(this).val()}),                                
                processResults: data => {
                    return { results: data.map(v => ({ text: v.title, id: v.id })) };
                },
            }
        });
        
    });

    // on contract change
    $('#contract').change(function() {
        // fetch schedules
        if (!$(this).val()) return;
        $("#schedule").html('').select2({
            allowClear: true,
            ajax: {
                url: "{{ route('biller.contracts.task_schedules')  }}",
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({
                    search: term, 
                    contract_id: $(this).val(),
                    is_report: 1
                }),                             
                processResults: data => {
                    return { 
                        results: data.map(v => {
                            let start = v.start_date ? v.start_date.split('-').reverse().join('-') : '';
                            let end = v.end_date ? v.end_date.split('-').reverse().join('-') : '';
                            let dt = start && end? ` (${start} - ${end})` : '';
                            return { text: v.title + dt, id: v.id };
                        }),
                    };
                },
            }
        });
    });

    // on add equipment
    const loadedIds = new Set();
    let rowIndx = 1;
    const rowHtml = $('#equipTbl tbody tr:eq(0)').html();
    $('#descr-0').autocomplete(completeEquip());
    $('#add_equip').click(function() {
        const i = rowIndx;
        let html = rowHtml.replace(/-0/g, '-'+i);
        $('#equipTbl tbody').append('<tr>' + html + '</tr>');
        $('#descr-'+i).autocomplete(completeEquip(i));
        rowIndx++;
    });

    //  on change bill
    $('#equipTbl').on('change', '.bill', () => calcTotal());    

    // on delete row
    $('#equipTbl').on('click', '.del', function() {
        const tr = $(this).parents('tr:first');
        const equipmentId = tr.find('input[name="equipment_id[]"]').val();
        loadedIds.delete(equipmentId*1);
        tr.remove();
        calcTotal();
    });
    
    // autocomplete equipment properties
    function completeEquip(i = 0) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#client_id").val(),
                    method: 'POST',
                    data: {
                        keyword: request.term, 
                        customer_id: $('#customer').val(),
                        branch_id: $('#branch').val(),
                        schedule_id: $('#schedule').val(),
                    },
                    success: data => {
                        // filter loaded ids
                        data = data.filter(v => ![...loadedIds].includes(v.id));
                        data = data.map(v => {
                            for (const key in v) {
                                if (!v[key]) v[key] = '';
                            }
                            const tid = `${v.tid}`.length < 4 ? `000${v.tid}`.slice(-4) : v.tid;
                            v.tid = `Eq-${tid}`;
                            
                            return {
                                label: `${v.tid} ${v.unique_id} ${v.equip_serial} ${v.make_type} ${v.model} ${v.machine_gas}
                                    ${v.capacity} ${v.location} ${v.building} ${v.floor}`,
                                value: `${[v.make_type, v.capacity].join('; ')}`,
                                data: v
                            }
                        });
                        return response(data)
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                $('#equipmentid-'+i).val(data.id);
                $('#location-'+i).text(data.location);
                $('#tid-'+i).text(data.tid);
                $('#rate-'+i).text(accounting.formatNumber(data.service_rate));
                $('#status-'+i).val(data.status);

                if(data.status === 'decommissioned' || data.status === 'cannibalised' || data.status === 'under warranty'){
                    $('#bill-'+i).val('0');
                }

                console.log("STATUS NI: " + data.status);

                calcTotal();
                loadedIds.add(data.id);
            }
        };
    }    

    // compute totals
    function calcTotal() {
        let rateTotal = 0;
        let billTotal = 0;
        $('#equipTbl tbody tr').each(function() {
            let isBill = $(this).find('.bill').val(); 
            let rate = accounting.unformat($(this).find('.rate').text());
            if (isBill == 1) billTotal += rate;
            rateTotal += rate;
        });
        $('#rate_ttl').val(accounting.formatNumber(rateTotal));
        $('#bill_ttl').val(accounting.formatNumber(billTotal));
    }
    if (@json(@$contractservice)) {
        calcTotal();
    }
</script>