{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}
<script>
    const config = {
        ajax: {
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}
        },
        date: {autoHide: true, format: '{{config('core.user_date_format')}}'},
    };
    // ajax header set up
    $.ajaxSetup(config.ajax);

    // modal submit callback
    function trigger(data) {
        switch (data.t_type) {
            case 1:
                $('#m_' + data.meta).remove();
                break;
            case 2:
                $('.timeline').prepend(data.meta);
                break;
            case 3:
                $(data.row).prependTo("#tasks-table tbody");
                $("#data_form_task").trigger('reset');
                break;
            case 5:
                $(data.meta).prependTo("#log-table  tbody");
                $("#data_form_log").trigger('reset');
                break;
            case 6:
                $(data.meta).prependTo("#notes-table  tbody");
                $("#data_form_note").trigger('reset');
                break;
            case 7:
                $("#data_form_quote").trigger('reset');
                break;
            case 8:
                $("#data_form_budget").trigger('reset');
                break;
            case 9:
                $("#data_form_invoice").trigger('reset');
                break;
        }
        return;
    }

    // on document load
    $(() => {
        // on show tab load datatables
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('project_tab', $(e.target).attr('href'));
            switch ($(e.target).attr('href')) {
                case '#tab_data2': milestone_unallocated_items(); break;
                case '#tab_data3': tasks(); break;
                case '#tab_data4': project_log(); break;
                case '#tab_data6': notes(); break;
                case '#tab_data10': invoices(); break;
                case '#tab_data7': quotes(); break;
                case '#tab_data8': budgets(); break;
                case '#tab_data9': expenses(); break;
                case '#tab_data13': boqs(); break;
                case '#tab_data14': boq_valuations(); break;
            }
        });
        const redirectToTab = "{{ request('tab') }}";
        if (redirectToTab == 'expense') localStorage.project_tab = '#tab_data9';
        // set active tab
        const projectTab = localStorage.project_tab;
        if (projectTab) $('a[href="' + projectTab + '"]').tab('show');


        // project progress slider
        $('#prog').text($('#progress').val() + '%');
        $(document).on('change', '#progress', function (e) {
            e.preventDefault();
            $('#prog').text($('#progress').val() + '%');
            $.ajax({
                url: "{{ route('biller.projects.update_status') }}",
                type: 'POST',
                data: {
                    project_id: "{{ $project->id }}",
                    r_type: '1',
                    progress: $('#progress').val(),
                },
                success: function(data) {
                    ['description', 'employee', 'assign', 'priority'].forEach(v => $('#'+v).html(data[v]));
                    $('#task_title').html(data.name);
                }
            });
        });

        $('#caption').on('keyup', function(e) {
            if($(this).val() != '') {
                $('#fileupload').removeAttr('style');
                $('#fileupload').show();
            }else{
                $('#fileupload').attr('style', ''); 
                $('#fileupload').hide();
            }
            // console.log($(this).val());
        });
        
        // file attachment upload
        $('#fileupload').fileupload({
            caption: $('#caption').val(),
            url: @json(route('biller.project_attachment')),
            dataType: 'json',
            formData: {},
            add: function (e, data) {
                // Get the additional input value
                var caption = $('#caption').val();
                
                // Append the additional data to the form data
                data.formData = { caption: caption, _token: "{{ csrf_token() }}", project_id: '{{$project['id']}}', 'bill': 11 };
                
                // Automatically start the upload
                data.submit();
            },
            done: function (e, data) {
                $.each(data.result, function (index, file) {
                    const del_url = @json(route('biller.project_attachment', '?op=delete&meta_id='));
                    const view_url = @json(asset('storage/app/public/files'));
                    const row = `
                    <tr>
                        <td width="5%">
                            <a href="${del_url}${file.id}" class="file-del red">
                                <i class="btn-sm fa fa-trash"></i>
                            </a>
                        </td>
                        <td>
                            <a href="${view_url}/${file.name}" target="_blank" class="purple">
                                <i class="btn-sm fa fa-eye"></i> ${file.name}
                            </a>
                        </td>
                        <td>
                            ${file.caption}
                        </td>
                    </tr>`;

                    $('#files').append(row);
                    window.location.reload();
                });
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css('width', progress + '%');
                console.log(this.caption);
            }
        })
        .prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

        // on delete file attachment
        $(document).on('click', ".file-del", function (e) {
            e.preventDefault();
            const obj = $(this);
            $.post($(this).attr('href'), data => {
                obj.parents('tr').remove()
            });
        });
    });

    // milestone show modal
    let milestoneState;
    const addMilestoneForm = $('#data_form_mile_stone')[0].outerHTML;
    $('#AddMileStoneModal').on('shown.bs.modal', function() {
        if (milestoneState == 'create') {
            $(this).find('.modal-content').html(addMilestoneForm);
            $('[data-toggle="datepicker"]').datepicker(config.date);
            $('.from_date').datepicker(config.date).datepicker('setDate', '{{dateFormat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d')))))}}');
            $('.to_date').datepicker(config.date).datepicker('setDate', 'today');
            $('#color').colorpicker();
        }

        // fetch milestone budget limit
        $.get("{{ route('biller.projects.budget_limit', $project) }}", ({data}) => {
            const budgetLimit = accounting.formatNumber(data.milestone_budget);
            $('.milestone-limit').text(budgetLimit);
            let limit = accounting.unformat($('.milestone-limit').text());
            if (milestoneState == 'edit') {
                const amount = accounting.unformat($('#milestone-amount').val());
                limit += amount;
                $('.milestone-limit').text(accounting.formatNumber(limit));
            } else {
                if (!limit || limit < 0) $('#milestone-amount').attr('disabled', true);
            }
        });

        $('#milestone-amount').change(function() {
            const milestoneBudget = accounting.unformat($('.milestone-limit').text());
            if (this.value > milestoneBudget) this.value = milestoneBudget;
            this.value = accounting.formatNumber(this.value);
        });

        // milestone submit
        $("#submit-data_mile_stone").on("click", function(e) {
            e.preventDefault();
            const amount = accounting.unformat($('#milestone-amount').val());
            if (!amount) return swal('Milestone amount required!');

            const form_data = {};
            form_data['form'] = $("#data_form_mile_stone").serialize();
            form_data['url'] = $('#action-url').val();
            // console.log(form_data);
            addObject(form_data, true);
            $('#AddMileStoneModal').modal('toggle');
            $('#data_form_mile_stone')[0].reset();
        });
    });
    $('#addMilestone').click(function() { milestoneState = 'create'; });
    
    // on edit milestone
    $(document).on('click', ".milestone-edit", function() {
        const obj = $(this);
        const url = $(this).attr('data-url');
        $.get(url, {object_id: $(this).attr('data-id'), obj_type: 2}, data => {
            milestoneState = 'edit';
            const div = $(document.createElement('div'));
            div.html(data);
            let form = div.find('.modal-content').html();
            $('#AddMileStoneModal').find('.modal-content').html(form);
            $('#AddMileStoneModal').modal('toggle');
        });
    });

    // on delete milestone
    $(document).on('click', ".milestone-del", function() {
        if (confirm('Are you sure to delete?')) {
            addObject({
                url: $(this).attr('data-url'),
                form: {object_id: $(this).attr('data-id'), obj_type: 2}
            }, true);
        }
    });

    // quote show modal
    $('#AddQuoteModal').on('shown.bs.modal', function () {
        const dt = "{{ dateFormat() }}";
        $('.from_date').val(dt);
        $('.to_date').val(dt);

        $("#quote").select2({
            allowClear: true,
            dropdownParent: $('#AddQuoteModal'),
            ajax: {
                url: "{{ route('biller.projects.quotes_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: @json(@$project->customer_id) }),
                processResults: (data) => {
                    return {
                        results: $.map(data, (item) => ({
                            text: `${item.name}`,
                            id: item.id
                        }))
                    };
                },
            }
        });
    });
    // detached invoice show modal
    $('#AddDetachedInvoiceModal').on('shown.bs.modal', function () {
        const dt = "{{ dateFormat() }}";
        $('.from_date').val(dt);
        $('.to_date').val(dt);

        $("#invoice").select2({
            allowClear: true,
            dropdownParent: $('#AddDetachedInvoiceModal'),
            ajax: {
                url: "{{ route('biller.projects.invoices_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: @json(@$project->customer_id) }),
                processResults: (data) => {
                    return {
                        results: $.map(data, (item) => ({
                            text: `${item.name}`,
                            id: item.id
                        }))
                    };
                },
            }
        });
    });

    //Attach DI to Quote
    $('#quotesTbl tbody').on('click', '.attach_di', function () {
        const el = $(this);
        const row = el.parents('tr:first');
        let quote_id = row.find('.attach_di').attr('data-id');
        $('#quote_id_val').val(quote_id);
        console.log(row.find('.attach_di').attr('data-id'));
    });

    // invoices show modal
    $('#AttachDIModal').on('shown.bs.modal', function () {
        const dt = "{{ dateFormat() }}";
        $('.from_date').val(dt);
        $('.to_date').val(dt);

        $("#dinvoice").select2({
            allowClear: true,
            dropdownParent: $('#AttachDIModal'),
            ajax: {
                url: "{{ route('biller.projects.invoices_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: @json(@$project->customer_id) }),
                processResults: (data) => {
                    return {
                        results: $.map(data, (item) => ({
                            text: `${item.name}`,
                            id: item.id
                        }))
                    };
                },
            }
        });
    });

    // task show modal
    $('#AddTaskModal').on('shown.bs.modal', function () {
        $('[data-toggle="datepicker"]').datepicker(config.date);
        $('.from_date').datepicker(config.date).datepicker('setDate', 'today');
        $('.to_date').datepicker(config.date).datepicker('setDate', '{{ dateFormat(date('Y-m-d', strtotime('+30 days', strtotime(date('Y-m-d'))))) }}');
        ['tags', 'employee', 'projects'].forEach(v => $('#'+v).select2());
        $('#color_t').colorpicker();
    });

    // on submit task
    $("#submit-data_tasks").on("click", function(e) {
        e.preventDefault();
        const form_data = {};
        form_data['form_name'] = 'data_form_task';
        form_data['form'] = $("#data_form_task").serialize();
        form_data['url'] = $('#action-url_task').val();
        addObject(form_data, true);
        $('#AddTaskModal').modal('toggle');
    });

    // on view task
    $(document).on('click', '.view_task', function() {
        const url = "{{ route('biller.tasks.load') }}";
        const task_id = $(this).attr('data-id');
        $('#progressTbl tbody').html('');
        $.post(url, {task_id}, data => {
            $('#t_name').html(data.name);
            $('#t_start').html(data.start)
            $('#t_end').html(data.duedate);
            $('#t_status').html(data.status);
            $('#t_status_list').html(data.status_list);
            $('#t_creator').html(data.creator);
            $('#t_assigned').html(data.assigned);
            $('#t_description').html(data.description);
            data.task_items.forEach((v,i) => {
                $('#progressTbl tbody').append(progressRow(v,i));
            });
        });
    });
    $(document).on('click', '.progress_task', function() {
        $('#date').datepicker(config.date).datepicker('setDate', 'today');
        const url = "{{ route('biller.tasks.load') }}";
        const task_id = $(this).attr('data-id');
        $.post(url, {task_id}, data => {
            $('#task_percent').val(data.task_completion);
            $('#project').val(data.project_id);
            $('#task').val(data.id);
        });
    });
    function progressRow(v,i){
        return `
            <tr>
                <td>${i+1}</td>
                <td>${v.date}</td>
                <td>${v.description}</td>
                <td>${v.type}</td>
                <td>${v.percent_qty}</td>
            </tr>
        `;
    }
    $(document).on('change', '#percent_qty, #type', function() {
        let percent_value = parseFloat($('#percent_qty').val());
        let task_completion = parseFloat($('#task_percent').val());
        let expected_percent_value = 0;
        let combined_percent_value = 0;
        if($('#type').val() == 'increment'){
            combined_percent_value = percent_value + task_completion;
            if(combined_percent_value > 100){
                expected_percent_value = 100 - task_completion;
                $('#percent_qty').val(expected_percent_value);
            }
        }else if($('#type').val() == 'decrement'){
            combined_percent_value = task_completion - percent_value;
            if(combined_percent_value < 1){
                expected_percent_value = task_completion;
                $('#percent_qty').val(expected_percent_value);
            }
        }
       
    });

    // on quote submit
    $("#submit-data_quote").on("click", function (e) {
        e.preventDefault();
        var form_data = {};
        form_data['form_name'] = 'data_form_quote';
        form_data['form'] = $("#data_form_quote").serialize();
        form_data['url'] = $('#action-url_7').val();
        addObject(form_data, true);
        $('#AddQuoteModal').modal('toggle');
    });

     // milestone show modal
     let noteState;
    const addNoteForm = $('#data_form_note')[0].outerHTML;
    $('#AddNoteModal').on('shown.bs.modal', function() {
        if (noteState == 'create') {
            $(this).find('.modal-content').html(addNoteForm);
            $('[data-toggle="datepicker"]').datepicker(config.date);
            $('.from_date').datepicker(config.date).datepicker('setDate', '{{dateFormat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d')))))}}');
            $('.to_date').datepicker(config.date).datepicker('setDate', 'today');
            $('#color').colorpicker();        
        }   

        
       // on submit note
        $("#submit-data_note").on("click", function(e) {
            e.preventDefault();
            const form_data = {};
            form_data['form_name'] = 'data_form_note';
            form_data['form'] = $("#data_form_note").serialize();
            form_data['url'] = $('#action-url_6').val();
            console.log(form_data);
            addObject(form_data, true);
            $('#AddNoteModal').modal('toggle');
            // window.location.reload();
        });        
    });
    
    $('#addNote').click(function() { noteState = 'create'; });

    // on edit note
    $(document).on('click', ".note-edit", function() {
        const obj = $(this);
        const url = $(this).attr('data-url');
        $.get(url, {object_id: $(this).attr('data-id'), obj_type: 6}, data => {
            noteState = 'edit';
            const div = $(document.createElement('div'));
            div.html(data);
            let form = div.find('.modal-content').html();
            $('#AddNoteModal').find('.modal-content').html(form);
            $('#AddNoteModal').modal('toggle');
        });
    }); 

    // on delete note
    $(document).on('click', ".note-del", function() {
        const obj = $(this);
        const url = $(this).attr('data-url');
        $.post(url, {object_id: $(this).attr('data-id'), obj_type: 6}, data => obj.parents('li').remove());
    });
    // on quote submit
    $("#submit-data_invoice").on("click", function (e) {
        e.preventDefault();
        var form_data = {};
        form_data['form_name'] = 'data_form_invoice';
        form_data['form'] = $("#data_form_invoice").serialize();
        form_data['url'] = $('#action-url_9').val();
        addObject(form_data, true);
        $('#AddDetachedInvoiceModal').modal('toggle');
    });
    // projects notes textarea ext
    $('.summernote').summernote({
        height: 300,
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['fullscreen', ['fullscreen']],
            ['codeview', ['codeview']]
        ],
        popover: {},
    });

    // Fetch quotes
    function quotes() {
        if ($('#quotesTbl tbody tr').length) return;
        $('#quotesTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.quotes.get') }}",
                type: 'POST',
                data: {project_id: "{{ $project->id }}"},
                dataSrc: ({data}) => {

                    data = data.map(v => {
                        if (v.budget_status.includes('budgeted')) {
                            v.actions = '';
                            if(v.invoice_tid == null){
                                v.actions += `
                                <button type="button" style="margin-top:5px;" class="btn btn-success btn-sm attach_di" id="attach_di" data-toggle="modal" data-id='${v.id}' data-target="#AttachDIModal">
                                    Attach Invoice <i class="fa fa-plus-circle"></i>
                                </button>
                                `;
                            }
                            return v;
                        }

                        const create_budget_url = @json(route('biller.budgets.create', 'quote_id=')) + v.id;
                        const detach_quote_url = @json(route('biller.projects.detach_quote', ['project_id' => $project->id])) + '&quote_id=' + v.id;
                        const id_quote = v.id;

                        if(v.invoice_tid == null){
                            // console.log(v.id);
                            v.actions = `
                                <div class="dropdown">
                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Action
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item create" href="${create_budget_url}"><i class="fa fa-plus-square-o" aria-hidden="true"></i> Budget</a>
                                            <a class="dropdown-item qt-detach text-danger" href="${detach_quote_url}" onclick="return confirm(\'Are you sure you want to Detach Quote From Project?\')"><i class="fa fa-trash text-danger" aria-hidden="true"></i> Detach</a>
                                        </div>
                                    </div>
                                <button type="button" style="margin-top:5px;" class="btn btn-success btn-sm attach_di" id="attach_di" data-toggle="modal" data-id='${id_quote}'
                                        data-target="#AttachDIModal">
                                        Attach Invoice <i class="fa fa-plus-circle"></i>
                                </button>
                            `;
                        }else{
                            v.actions = `
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Action
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item create" href="${create_budget_url}"><i class="fa fa-plus-square-o" aria-hidden="true"></i> Budget</a>
                                        <a class="dropdown-item qt-detach text-danger" href="${detach_quote_url}" onclick="return confirm(\'Are you sure you want to Detach Quote From Project?\')"><i class="fa fa-trash text-danger" aria-hidden="true"></i> Detach</a>
                                    </div>
                                </div>
                        `;

                        }
                        return v;
                    });

                    return data;
                }
            },
            columns: [
                {data: 'DT_Row_Index',name: 'id'},
                ...['tid', 'customer', 'notes', 'total', 'invoice_tid', 'budget_status']
                .map(v => ({data: v, name: v})),
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: 5 },
                { type: "custom-date-sort", targets: 1 }
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
    // detach quote
    $(document).on('click', ".qt-detach", function(e) {
        e.preventDefault();
        addObject({form: '', url: $(this).attr('href')}, true);
        $(this).parents('tr').remove();
    });

    // Fetch budget
    function budgets() {
        if ($('#budgetsTbl tbody tr').length) return;
        $('#budgetsTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.budgets.get') }}",
                type: 'POST',
                data: {project_id: "{{ $project->id }}"},
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                ...['tid', 'customer', 'quote_total', 'budget_total'].map(v => ({data: v, name: v})),
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: 5 },
                { type: "custom-date-sort", targets: 1 }
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    // fetch expenses
    ['accountLedger', 'supplier', 'product_name', 'budget_line_filter'].forEach(v => $('#'+v).select2({allowClear: true}));
    ['expCategory', 'accountLedger', 'supplier', 'product_name', 'budget_line_filter'].forEach(v => $('#'+v).change(() =>  expenses(render=true)));
    function expenses(render=false) {
        if (!render) {
            if ($('#expItems tbody tr').length) return;
        } else $('#expItems').DataTable().destroy();

        $('#expItems').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.projects.get_expense') }}",
                type: 'POST',
                data: {
                    project_id: "{{ $project->id }}",
                    exp_category: $('#expCategory').val(),
                    ledger_id: $('#accountLedger').val(),
                    supplier_id: $('#supplier').val(),
                    product_name: $('#product_name').val(),
                    budget_line_filter: $('#budget_line_filter').val(),
                },
                dataSrc: ({data}) => {
                    if (data.length) {
                        const groupTotals = data[0]['group_totals'];
                        $('#expTotals tbody td').each(function(i) {
                            let row = $(this);
                            switch (i) {
                                case 0: row.text(accounting.formatNumber(groupTotals['inventory_stock'])); break;
                                case 1: row.text(accounting.formatNumber(groupTotals['labour_service'])); break;
                                case 2: row.text(accounting.formatNumber(groupTotals['dir_purchase_stock'])); break;
                                case 3: row.text(accounting.formatNumber(groupTotals['dir_purchase_service'])); break;
                                case 4: row.text(accounting.formatNumber(groupTotals['purchase_order_stock'])); break;
                                case 5: row.text(accounting.formatNumber(groupTotals['grand_total'])); break;
                            }
                        });
                    }
                    return data;
                },
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                ...['exp_category', 'milestone', 'supplier', 'product_name','date', 'uom', 'qty', 'rate', 'amount']
                .map(v => ({data: v, name: v})),
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    /**Fetch Invoices */
    function invoices() {
        if ($('#invoices-table_p tbody tr').length) return;
        $('#invoices-table_p').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.invoices.get') }}",
                type: 'POST',
                data: {project_id: "{{ $project->id }}"},
                dataSrc: ({data}) => {

                    data = data.map(v => {
                        const detach_quote_url = @json(route('biller.projects.detach_invoice', ['project_id' => $project->id])) + '&invoice_id=' + v.id;
                        // console.log(v.is_standard);
                        if(v.is_standard == 1){
                            v.actions = `
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Action
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                        <a class="dropdown-item qt-detach text-danger" href="${detach_quote_url}"><i class="fa fa-trash text-danger" aria-hidden="true"></i> Detach</a>
                                    </div>
                                </div>
                        `;
                        }else{
                            v.actions = ``;
                        }

                        return v;
                    });

                    return data;
                }
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                ...['tid', 'customer', 'invoicedate', 'total', 'status', 'invoiceduedate'].map(v => ({data:v, name:v})),
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    //Fetch Notes
    function notes() {
        if ($('#notes-table tbody tr').length) return;
        $('#notes-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {@lang('datatable.strings') },
            ajax: {
                url: '{{ route("biller.notes.get") }}',
                type: 'POST',
                data: {project_id: "{{ $project->id }}"},
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                ...['title', 'content', 'user', 'created_at'].map(v => ({data:v, name:v})),
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

     // Fetch boq
    function boqs() {
        if ($('#boqsTbl tbody tr').length) return;
        $('#boqsTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.boqs.get') }}",
                type: 'POST',
                data: {project_id: "{{ $project->id }}"},
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                ...['tid', 'customer', 'name','boq_total','total_boq_vat'].map(v => ({data: v, name: v})),
                {data: 'grand_total', name: 'grand_total', searchable: false, sortable: false}
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: 4 },
                { type: "custom-date-sort", targets: 1 }
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

     // Fetch boq valuation
    function boq_valuations() {
        if ($('#boqValuationsTbl tbody tr').length) return;
        $('#boqValuationsTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.boq_valuations.get') }}",
                type: 'POST',
                data: {project_id: "{{ $project->id }}"},
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                ...['tid', 'customer','quote_amount','subtotal','balance','date'].map(v => ({data: v, name: v})),
                {data: 'invoice_tid', name: 'invoice_tid', searchable: false, sortable: false}
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: 4 },
                { type: "custom-date-sort", targets: 1 }
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    /**Fetch Tasks */
    function tasks() {
        if ($('#tasks-table tbody tr').length) return;
        $('#tasks-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: '{{ route("biller.tasks.get") }}',
                type: 'POST',
                data: {project_id: "{{ $project->id }}"},
            },
            columns: [
                {data: 'DT_Row_Index'},
                ...['milestone', 'name', 'start', 'duration', 'duedate', 'status', 'assigned_to', 'task_completion'].map(v => ({data:v, name:v})),
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    /**Fetch activity Logs*/
    function project_log() {
        if ($('#log-table tbody tr').length) return;
        $('#log-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: '{{ route("biller.projects.log_history") }}',
                type: 'post',
                data: {project_id: @json(@$project->id)},
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'created_at', name: 'created_at'},
                {data: 'user', name: 'user'},
                {data: 'value', name: 'value'},
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    function milestone_unallocated_items() {
        if ($('#milestonesTbl tbody tr').length) return;
        $('#milestonesTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.milestones.milestone_unallocated_items') }}",
                type: 'POST',
                data: {project_id: "{{ $project->id }}"},
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                @if (auth()->user()->customer_id)
                ...['name', 'code', 'uom','allocated_qty','qty', 'selling_price'].map(v => ({data: v, name: v})),
                @else
                ...['name', 'code', 'uom','allocated_qty','qty','purchase_price','selling_price','difference'].map(v => ({data: v, name: v})),
                {data: 'percentage', name: 'percentage', searchable: false, sortable: false}
                @endif
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
    $('#milestone_id').select2({allowClear: true});

    $('#milestone_id').change(function(){
        const milestone_id = $('#milestone_id').val();
        $('#milestonesTbl tbody').html('');
        $.ajax({
            url: "{{ route('biller.milestones.items') }}",
            method: 'POST',
            data: {
                milestone_id : milestone_id,
            },
            success: function (data){
                console.log(data)
                data.forEach((v,i) => {
                    $('#milestonesTbl tbody').append(milestoneRow(v,i));
                });
            }
        });
    })

    function milestoneRow(v,i) {
        return `
                <tr>
                    <td>${i+1}</td>    
                    <td class="text-left">${v.name}</td>
                    <td>${v.code}</td>  
                    <td>
                        ${v.uom}
                    </td>     
                    <td>${accounting.formatNumber(v.allocated_qty)}</td>    
                    <td class="qty_ordered">${accounting.formatNumber(v.qty)}</td>    
                    <td class="qty_received">${accounting.formatNumber(v.purchase_price)}</td>    
                    <td class="qty_due">${accounting.formatNumber(v.price)}</td>    
                    <td>${accounting.formatNumber(v.difference)}</td>    
                    <td>${accounting.formatNumber(v.percentage)}</td>    
                    
                </tr>
            `;
    }
</script>