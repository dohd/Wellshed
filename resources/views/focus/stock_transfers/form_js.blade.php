{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        autoCompleteCb: () => {
            return {
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('biller.products.quote_product_search') }}",
                        data: {keyword: request.term, warehouse_id: $('#source').val()},
                        method: 'POST',
                        success: result => response(result.map(v => ({
                            label: `${v.name}`,
                            value: v.name,
                            data: v
                        }))),
                    });
                },
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    const {data} = ui.item;
                    let row = Index.currRow;
                    row.find('.prodvar-id').val(data.id); 
                    row.find('.qty-onhand').text(accounting.unformat(data.qty));
                    row.find('.qty-onhand-inp').val(accounting.unformat(data.qty));
                    row.find('.qty-rem').text(accounting.unformat(data.qty));
                    row.find('.qty-rem-inp').val(accounting.unformat(data.qty));
                    row.find('.cost').val(accounting.unformat(data.purchase_price));
                    row.find('.qty-transf').val('');
                    if (data.units && data.units.length) {
                        const unit = data.units[0];
                        row.find('.unit').text(unit.code);
                    }
                }
            };
        }
    };

    const Index = {
        currRow: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#name-' + 1).autocomplete(config.autoCompleteCb());
            ['#source', '#dest'].forEach(v => $(v).select2({allowClear: true}));
            $('#lead_id').select2({
                allowClear: true
            });

            $('#add-item').click(Index.addItemClick);
            $('#source').change(Index.sourceChange);
            $('#productsTbl').on('keyup', '.qty-transf', Index.qtyKeyUp);
            $('#productsTbl').on('keyup', '.name', function() { Index.currRow = $(this).parents('tr') });
            $('#productsTbl').on('click', '.remove', Index.removeRowClick);

            const data = @json(@$stock_transfer);
            const data_items = @json(@$stock_transfer->items);
            if (data && data_items.length) {
                $('.datepicker').datepicker('setDate', new Date(data.date));
                $('#productsTbl tbody tr').each(function(i) {
                    const v = data_items[i];
                    const row = $(this);
                    if (i > 0) row.find('.name').autocomplete(config.autoCompleteCb());
                    row.find('.amount').val(v.amount*1);
                    row.find('.cost').val(v.cost*1);
                    row.find('.qty-rem-inp').val(v.qty_rem*1);
                    row.find('.qty-onhand-inp').val(v.qty_onhand*1);
                    row.find('.prodvar-id').val(v.productvar_id);
                });
                Index.calcTotals();
            }
        },

        sourceChange() {
            $('#productsTbl tbody tr:not(:first)').remove();
            $('#productsTbl .remove:first').click();
        },

        addItemClick() {
            let row = $('#productsTbl tbody tr:last').clone();
            let indx = accounting.unformat(row.find('.name').attr('id').split('-')[1]);
            row.find('input, textarea').val('').attr('value', '');
            row.find('textarea').text('');
            row.find('.unit, .qty-onhand, .qty-rem').text('');
            row.find('.name').attr('id', `name-${indx+1}`);
            $('#productsTbl tbody').append(`<tr>${row.html()}</tr>`);
            $(`#name-${indx+1}`).autocomplete(config.autoCompleteCb());
        },

        removeRowClick() {
            let row = $(this).parents('tr');
            if (!row.siblings().length) {
                row.find('input, textarea').each(function() { $(this).val(''); });
                row.find('textarea').text('');
                row.find('.unit, .qty-onhand, .qty-rem').text('');
            } else row.remove();
            Index.calcTotals();
        },

        qtyKeyUp() {
            const row = $(this).parents('tr');
            const cost = accounting.unformat(row.find('.cost').val());
            const qtyOnhand = accounting.unformat(row.find('.qty-onhand').text());
            let qtyTransf = accounting.unformat(row.find('.qty-transf').val());
            if (qtyTransf < 0) qtyTransf = 0;
            if (qtyTransf > qtyOnhand) {
                qtyTransf = qtyOnhand;
                row.find('.qty-transf').val(qtyTransf);
            }
            const amount = qtyTransf * cost;
            qtyRem = qtyOnhand - qtyTransf;
            row.find('.qty-rem').text(qtyRem);
            row.find('.qty-rem-inp').val(qtyRem);
            row.find('.amount').val(accounting.formatNumber(amount));
            Index.calcTotals();
        },  

        calcTotals() {
            let total = 0;
            $('#productsTbl tbody tr').each(function() {
                const amount = accounting.unformat($(this).find('.amount').val());
                total += amount;
            });
            $('#total').val(accounting.formatNumber(total));
        },
    };

    // select2 config
    function select2Config(url, callback) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: callback
            },
            allowClear: true,
        }
    }


    // load projects dropdown
    const projectUrl = "{{ route('biller.projects.project_search') }}";
    function projectData(data) {
        data = data.map(v => ({id: v.id, text: v.name, budget: v.budget ? v.budget.budget_total : 0 }));
        loadedProjectDetails = data;
        return {results: data};
    }

    $("#project").select2(select2Config(projectUrl, projectData));
    $("#employee_id").select2({allowClear: true});


    function getProjectMilestones(projectId, forItems = false, inputClass = ''){
        //console.log(projectId);
        $.ajax({
            url: "{{ route('biller.getProjectMileStones') }}",
            method: 'GET',
            data: { projectId: projectId},
            dataType: 'json', // Adjust the data type accordingly
            success: function(data) {
                // This function will be called when the AJAX request is successful
                var select = null;
                if(forItems === false) select = $('#project_milestone');
                else if(forItems === true) select = $('.item-milestone');
                else if(forItems === false && inputClass !== ''){
                    select = $(inputClass);
                    //console.log("ITEM CLASS ID NI: " + select.id);
                }
                // Clear any existing options
                select.empty();
                if(data.length === 0){
                    select.append($('<option>', {
                        value: '',
                        text: 'No Milestones Created For This Project'
                    }));
                } else {
                    select.append($('<option>', {
                        value: '',
                        text: 'Select a Budget Line'
                    }));
                    // Add new options based on the received data
                    for (var i = 0; i < data.length; i++) {
                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        const date = new Date(data[i].due_date);
                        select.append($('<option>', {
                            value: data[i].id,
                            text: data[i].name + ' | Balance: ' +  parseFloat(data[i].balance).toFixed(2) + ' | Due on ' + date.toLocaleDateString('en-US', options)
                        }));
                    }
                    let selectedOptionValue = "{{ @$stock_transfer->project_milestone }}";
                    if (selectedOptionValue) {
                        select.val(selectedOptionValue);
                    }
                }
            },
            error: function() {
                // Handle errors here
                //console.log('Error loading data');
            }
        });
    }

    //Load Milestones
    $('#project').change(function() {
        getProjectMilestones($(this).val())
        getProjectMilestones($(this).val(), true);
    });

    $(document).ready(function () {

        @php
            $project_name = '';
            $project = @$stock_transfer->project;
            if ($project) {
                $tid = gen4tid('Prj-', $project->tid);
                $customer = '';
                if ($project->customer_project) $customer = $project->customer_project->company;
                if ($customer && $project->branch) $customer .= " - {$project->branch->name}";
                $project_name = "{$customer} - {$tid} - {$project->name}";

            }
        @endphp

        console.clear();
        console.log("{{ $project_name }}");

        const projectName = "{{ $project_name }}";
        const projectId = "{{ @$stock_transfer->project_id }}";
        if (projectId > 0) $('#project').append(new Option(projectName, projectId, true, true)).change();
    });


    $(Index.init);
</script>
