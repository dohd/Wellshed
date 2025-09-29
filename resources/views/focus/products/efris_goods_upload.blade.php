@extends ('core.layouts.app')
@section ('title', 'EFRIS Goods Upload')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-6 pt-1">
            <h4 class="content-header-title">EFRIS Goods Upload</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group">
                        <a href="{{ route('biller.products.efris_goods_config') }}" class="btn btn-info  btn-lighten-2">
                            <i class="fa fa-cloud-upload"></i> Goods Assigning
                        </a>
                        <a href="{{ route('biller.products.index') }}" class="btn btn-danger  btn-lighten-2">
                            <i class="fa fa-list-alt"></i> List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">                               
                                <!-- Product List -->
                                <div class="col-md-12">
                                    <div class="row mb-1">                          
                                        <div class="col-md-2">
                                            <select id="productStatus" class="custom-select" style="height: 2.5em; margin-top: .5em;">
                                                <option value="">-- Filter By Status --</option>
                                                <option value="not-uploaded">N/Uploaded</option>
                                                <option value="uploaded">Uploaded</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 pt-1 text-center">
                                            <h5><b>No. Products Selected: </b><span id="productSel">0</span></h5>
                                        </div>
                                        <div class="col-md-2">
                                            <button id="configureBtn" class="btn btn-success" data-toggle="modal" data-target="#configureModal">
                                                Configure Selected
                                            </button>
                                        </div>
                                    </div>
                                    <hr>
                                    
                                    <div class="table-responsive">
                                        <table id="productsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="checkAll"></th> 
                                                    <th>Item Code</th>
                                                    <th>Goods Name</th>
                                                    <th>Category</th>
                                                    <th>Commodity Id</th>
                                                    <th>Goods Code</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="100%" class="text-center text-success font-large-1">
                                                        <i class="fa fa-spinner spinner"></i>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{ Form::open(['route' => 'biller.products.efris_assign_commodity_code', 'method' => 'POST']) }}
            <input type="hidden" name="productvar_ids" id="productvarIds">
        {{ Form::close() }}
    </div>
    @include('focus.products.partials.configure-modal')
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script>
    $('#productConfigTbl thead th').css({paddingBottom: '1rem', paddingTop: '1rem'});
    $('#productConfigTbl tbody td').css({paddingLeft: '0.50rem', paddingRight: '0.50rem'});
    $('#productConfigTbl thead').css({position: 'sticky', top: 0, zIndex: 100});

    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }}
    };
    
    const Index = {
        goodsRowHtml: $('#productConfigTbl tbody tr:first').html(),

        init() {
            $.ajaxSetup(config.ajax);
            $('#category').select2({allowClear: true});

            $('#checkAll').change(Index.checkAllRows);
            $('#productvarIds').change(Index.setProductSelected);

            $('#configureModal').on('shown.bs.modal', Index.onShownModal);
            $(document).on('change', '.check-row', Index.checkRow);
            $('#productStatus').change(Index.filterChange);

            Index.drawData();
        },

        onShownModal() {
            $.post("{{ route('biller.products.efris_goods_modal_data') }}", {
                productvar_ids: $('#productvarIds').val(),
            })
            .then(data => {
                if (data && data.length) {
                    const tbody = $('#productConfigTbl tbody');
                    tbody.html('');
                    data.forEach(v => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = Index.goodsRowHtml;
                        $(tr).find('.productvar-id').val(v.id);
                        $(tr).find('.commodity-code').val(v.efris_commodity_code);
                        $(tr).find('.commodity-code-txt').html(v.efris_commodity_code);
                        $(tr).find('.goods-name').val(v.name);
                        $(tr).find('.goods-name-txt').html(v.name);
                        $(tr).find('.goods-code').val(v.efris_goods_code);
                        $(tr).find('.measure-unit').append(`<option value="${v.efris_unit}">${v.efris_unit_name}</option>`);
                        $(tr).find('.unit-price').val(v.unit_price);
                        $(tr).find('.stock-prewarning').val(v.alert);
                        $(tr).find('.have-piece-unit').val(v.have_piece_unit);
                        $(tr).find('.piece-unit-price').val(v.piece_unit_price);
                        $(tr).find('.piece-measure-unit').append(`<option value="${v.piece_measure_unit}">${v.piece_measure_unit_name}</option>`);
                        $(tr).find('.package-scaled-value').val(v.package_scaled_value);
                        $(tr).find('.piece-scaled-value').val(v.piece_scaled_value);
                        tbody.append(tr);
                    });
                }
            })
            .fail((xhr,status, error) => console.log(error));
        },

        clickconfigureBtn() {
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

        setProductSelected() {
            const productvarIds = $('#productvarIds').val()? $('#productvarIds').val().split(',') : [];
            $('#productSel').html(productvarIds.length);
        },

        checkAllRows() {
            const validCheckboxes = $('.check-row:not(:disabled)');
            if ($(this).prop('checked')) {
                const productvarIds = [];
                validCheckboxes.prop('checked', true);
                validCheckboxes.each(function() {
                    productvarIds.push($(this).attr('data-id'));
                });
                $('#productvarIds').val(productvarIds.join(',')).change();
            } else {
                validCheckboxes.prop('checked', false);
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
                        product_status: $('#productStatus').val(),
                        is_goods_upload: true,
                    },
                },
                columns: [
                    {data: 'row_check', name: 'row_check', sortable: false, searchable: false},
                    ...['code', 'name', 'category', 'efris_commodity_code', 'goods_code'].map(v => ({data: v, name: v})),
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
@endsection
