{{ Html::script('focus/js/select2.min.js') }}
<script>
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
        projectSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.projects.project_load_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customer").val()}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        }
    };

    const Index = {
        customer_complains: @json(@$customer_complains),
        init(){
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $.ajaxSetup(config.ajax);
            $('#employees').select2({allowClear: true});
            $('#solver').select2({allowClear: true});
            $("#customer").select2({allowClear: true}).change(Index.onChangeCustomer);
            $("#project").select2(config.projectSelect).change();
            $('#prog').text($('#initial_scale').val());
            $(document).on('change', '#initial_scale', function (e) {
                e.preventDefault();
                $('#prog').text($('#initial_scale').val());
            });
            $('#prog1').text($('#final_scale').val());
            $(document).on('change', '#final_scale', function (e) {
                e.preventDefault();
                $('#prog1').text($('#final_scale').val());
            });

            if(this.customer_complains){
                const projectName = "{{ @$customer_complains->project->name }}";
                const projectId = "{{ @$customer_complains->project_id }}";
                $('#project').append(new Option(projectName, projectId, true, true)).change();
            }

        },
        onChangeCustomer() {
            $("#project option:not(:eq(0))").remove();
        }
    };
    $(()=> Index.init());
</script>