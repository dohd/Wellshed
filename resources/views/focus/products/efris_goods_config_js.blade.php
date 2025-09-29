{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }}
    };
    
    const Index = {
        commodityListHtml: $('.commodity-list-ctn').clone(),

        init() {
            $.ajaxSetup(config.ajax);
            $('#category').select2({allowClear: true});

            $('#checkAll').change(Index.checkAllRows);
            $('#productvarIds').change(Index.setProductSelected);
            $('#assignBtn').click(Index.clickAssignBtn);
            $('#searchGoodsCode').keyup(Index.searchGoodsCode);

            $(document).on('change', '.check-row', Index.checkRow);
            $(document).on('click', '.tree-node', Index.fetchChildNodes);
            $(document).on('click', '.level', Index.setCommiditySelected);
            $(document).on('change', '#category, #warehouse', Index.filterChange);
            $(document).on('change', '#productvarIds, #commodityCode', Index.enableAssignBtn);

            Index.drawData();
        },

        clickAssignBtn() {
            swal({
                title: 'Are You  Sure?',
                text: "Once applied, you will not be able to undo!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((isOk) => {
                if (isOk) {
                    $('form').submit();
                }
            }); 
        },

        enableAssignBtn() {
            if ($('#productvarIds').val() && $('#commodityCode').val()) {
                $('#assignBtn').attr('disabled', false);
            } else {
                $('#assignBtn').attr('disabled', true);
            }
        },

        setCommiditySelected() {
            $('#commoditySel').html($(this).html());
            $('#commodityCode').val($(this).attr('min_commodity_code')).change();
        },

        setProductSelected() {
            const productvarIds = $('#productvarIds').val()? $('#productvarIds').val().split(',') : [];
            $('#productSel').html(productvarIds.length);
        },

        searchGoodsCode() {
            $('.commodity-list-ctn').html(`<div class="text-center"><i class="fa fa-spinner spinner"></i></div>`);
            if (!$(this).val()) {
                return $('.commodity-list-ctn').html(Index.commodityListHtml.html());
            } 

            $.post("{{ route('biller.products.efris_goods_code_search') }}", {
                search: $(this).val(),
            })
            .then(html => {
                $('.commodity-list-ctn').html(html);
            })
            .fail((xhr, status, error) => {
                console.log(error);
                $('.commodity-list-ctn').html(`<div class="text-center text-danger h5">Something went wrong! Try again later</div>`);
            });
        },

        fetchChildNodes() {
            const node = $(this)[0];
            if ($(this).next().length) {
                let nested = node.nextElementSibling;
                nested.classList.toggle("active");
                node.innerHTML = nested.classList.contains("active") ? "▼ " + node.textContent.slice(2) : "▶ " + node.textContent.slice(2);
            } else {
                $.post("{{ route('biller.products.efris_goods_config_data') }}", {
                    min_family_code: $(this).attr('min_family_code'),
                    max_family_code: $(this).attr('max_family_code'),
                    min_class_code: $(this).attr('min_class_code'),
                })
                .then(data => {
                    if (!data.length) return;

                    $(this).siblings().remove();
                    if ($(this).hasClass('level-0')) {
                        const li = data.map(v => `<li><span class="tree-node level-1" min_family_code="${v.min_family_code}">▶ (${v.min_family_code}) ${v.family_name}</span></li>`);
                        $(this).after(`<ul class="nested">${li.join('')}</ul>`);
                    } else if ($(this).hasClass('level-1')) {
                        const li = data.map(v => `<li><span class="tree-node level-2" min_class_code="${v.min_class_code}">▶ (${v.min_class_code}) ${v.class_name}</span></li>`);
                        $(this).after(`<ul class="nested">${li.join('')}</ul>`);
                    } else if ($(this).hasClass('level-2')) {
                        const li = data.map(v => `<li><span class="tree-node level" min_commodity_code="${v.min_commodity_code}">(${v.min_commodity_code}) ${v.commodity_name}</span></li>`);
                        $(this).after(`<ul class="nested">${li.join('')}</ul>`);
                    }

                    let nested = node.nextElementSibling;
                    if (nested) {
                        nested.classList.toggle("active");
                        node.innerHTML = nested.classList.contains("active") ? "▼ " + node.textContent.slice(2) : "▶ " + node.textContent.slice(2);
                    }
                })
                .fail((xhr, status, error) => console.log(error));
            }
        },

        checkAllRows() {
            if ($(this).prop('checked')) {
                const productvarIds = [];
                $('.check-row').prop('checked', true);
                $('.check-row').each(function() {
                    productvarIds.push($(this).attr('data-id'));
                });
                $('#productvarIds').val(productvarIds.join(',')).change();
            } else {
                $('.check-row').prop('checked', false);
                $('#productvarIds').val('').change();
            }
        },

        checkRow() {
            const id = $(this).attr('data-id');
            const productvarIds = $('#productvarIds').val()? $('#productvarIds').val().split(',') : [];
            if ($(this).prop('checked')) {
                productvarIds.push(id);
            } else {
                productvarIds.splice(productvarIds.indexOf(id), 1);
            }
            $('#productvarIds').val(productvarIds.join(',')).change();
        },

        filterChange() {
            if (!$('#category').val()) 
                return alert('Category is required!');
            $('#productsTbl').DataTable().destroy();
            return Index.drawData();
        },

        drawData() {
            $('#productsTbl').dataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                language: {@lang("datatable.strings")},
                ajax: {
                    url: '{{ route("biller.products.efris_goods_config_productvar_data") }}',
                    type: 'post',
                    data: {
                        category_id: $('#category').val(),
                        warehouse_id: $('#warehouse').val(),
                        is_goods_config: true,
                    },
                },
                columns: [
                    {data: 'row_check', name: 'row_check', sortable: false, searchable: false},
                    ...[
                        'code', 'name', 'category', 'efris_commodity_code',
                    ].map(v => ({name: v, data: v})),
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
