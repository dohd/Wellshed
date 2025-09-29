
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
{{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        branchSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customerFilter").val()}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        },
        quoteSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.projects.quotes_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#person").val(), branch_id: $("#branch_id").val() }),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        }
    };

    // form submit callback
    function trigger(res) {
        $('#projectsTbl').DataTable().destroy();
        Index.drawDataTable();
    }

    const Index = {
        startDate: '',
        endDate: '',
        
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            
            $("#wip_account").select2({allowClear: true, dropdownParent: $('#AddProjectModal')});
            $("#submit-data_project").on("click", Index.onSubmitProject);
            $("#projectStatus").change(Index.onChangeStatus);
            $("#customerFilter").select2({allowClear: true}).change(Index.onChangeCustomer);
            $("#branchFilter").select2(config.branchSelect).change(Index.onChangeBranch);
            $('#AddProjectModal').on('shown.bs.modal', Index.onShownModal);
            $(document).on('click', '.status', Index.onStatusClick);
            $('#search').click(Index.onClickSearch);
            Index.drawDataTable();
        },
        
        onClickSearch() {
            Index.startDate = $('#start_date').val();
            Index.endDate = $('#end_date').val();
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable(); 
        },

        onStatusClick() {
            $('#status_project_id').val($(this).attr('project-id'));
            $('#status').val($(this).attr('data-id'));
            $('#end_note').val($(this).attr('end-note'));
        },

        onSubmitProject(e) {
            // Step 1: Let HTML5 validation occur
            const form = document.getElementById("data_form_project");

            if (!form.checkValidity()) {
                // Let the browser show native error messages
                return;
            }

            // Step 2: Prevent default form submission
            e.preventDefault();

            // Step 3: Gather form data
            let form_data = {};
            form_data['form'] = $("#data_form_project").serialize();
            form_data['url'] = $('#action-url').val();

            // Step 4: Toggle modal and submit via custom function
            $('#AddProjectModal').modal('toggle');
            addObject(form_data, true);
        },

        onChangeCustomer() {
            $("#branchFilter option:not(:eq(0))").remove();
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        onChangeBranch() {
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable(); 
        },
        
        onChangeStatus() {
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable(); 
        },

        onShownModal() {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: "{{ config('core.user_date_format') }}"
            });
            $('.from_date').datepicker(config.date);
            $('.to_date').datepicker(config.date);
            $('#color').colorpicker();
            $("#tags").select2();
            $("#employee").select2();

            const branchConfig = {...config.branchSelect};
            branchConfig.ajax.data = ({term}) => ({search:term, customer_id: $('#person').val()});
            branchConfig.dropdownParent = $('#AddProjectModal');
            $("#branch_id").select2(branchConfig);

            $("#person").select2({allowClear: true, dropdownParent: $('#AddProjectModal')})
            .change(function() { $("#branch_id").val('') });
            
            // attach primary quote
            $("#quotes").select2(config.quoteSelect).change(function() {
                $('.proj_title').val('');
                $('.proj_short_descr').val('');
                let text = $("#quotes option:eq(1)").text();
                if (text) {
                    text = text.split('-')[2];
                    $('.proj_title').val(text);
                    $('.proj_short_descr').val(text);
                }
            });
        },

        drawDataTable() {
            $('#projectsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.projects.get') }}",
                    type: 'POST',
                    data: {
                        customer_id: $("#customerFilter").val(),
                        branch_id: $("#branchFilter").val(),
                        status: $("#projectStatus").val(),
                        start_date: Index.startDate,
                        end_date: Index.endDate,
                    }
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...['tid', 'main_quote_id', 'name', 'wip_account', 'exp_profit_margin','progress', 'priority', 'status', 'job_hrs', 'start_date', 'end_date'].map(v => ({data: v, name: v})),
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [4] },
                    // { type: "custom-date-sort", targets: [1,6] }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(Index.init);
</script>
