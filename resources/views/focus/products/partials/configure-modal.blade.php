<!-- cancel -->
<div id="configureModal" class="modal fade">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">EFRIS Goods Configuration</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">   
                {{ Form::open(['route' => 'biller.products.efris_goods_upload', 'method' => 'POST']) }}             
                    <fieldset class="border p-0 pl-1 pr-1 mb-1">
                        <legend class="w-auto float-none h5">Goods Details</legend>
                        <div class="table-responsive">
                            <table id="productConfigTbl" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%" style="max-height: 550px; overflow-y: auto;">
                                <thead>
                                    <tr style="background: #F6F9FD">
                                        <th>Commodity Code</th>
                                        <th>Goods Name</th>
                                        <th>Goods Code</th>
                                        <th>Measure Unit</th>
                                        <th>Unit Price</th>
                                        <th>Currency</th>
                                        <th>Stock Prewarning</th>
                                        <th>Have Excise Tax</th>
                                        <th>Have Piece Unit</th>
                                        <th>Piece Unit Price</th>
                                        <th>Piece Measure Unit</th>
                                        <th>Package Scaled Value</th>
                                        <th>Piece Scaled Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <input type="hidden" class="productvar-id" name="productvar_id[]">
                                        <input type="hidden" class="commodity-code" name="commodity_category_id[]">
                                        <input type="hidden" class="goods-name" name="goods_name[]">
                                        <!--td -->
                                        <td class="commodity-code-txt"></td>
                                        <td class="goods-name-txt" style="min-width: 30em;"></td>
                                        <td><input type="text" class="form-control goods-code" name="goods_code[]"></td>
                                        <td><select class="custom-select measure-unit" name="measure_unit[]"></select></td>
                                        <td><input type="text" class="form-control unit-price" name="unit_price[]" readonly></td>
                                        <td>
                                            <select class="custom-select currency" name="currency[]">
                                                <option value="{{ $currencies[0]->efris_currency }}">{{ $currencies[0]->efris_currency_name }}</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control stock-prewarning" name="stock_prewarning[]" readonly></td>
                                        <td>
                                            <select class="custom-select have-excise-tax" name="have_excise_tax[]">
                                                <option value="102">NO</option>
                                                <option value="101">YES</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="custom-select have-piece-unit" name="have_piece_unit[]">
                                                <option value="101">YES</option>
                                                <option value="102">NO</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control piece-unit-price" name="piece_unit_price[]"></td>
                                        <td><select class="custom-select piece-measure-unit" name="piece_measure_unit[]"></select></td>
                                        <td><input type="text" class="form-control package-scaled-value" name="package_scaled_value[]"></td>
                                        <td><input type="text" class="form-control piece-scaled-value" name="piece_scaled_value[]"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                    <div class="modal-footer">                        
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{trans('general.close')}}</button>
                        <button type="submit" id="saveAndUploadBtn" class="btn btn-success"><i class="fa fa-exclamation-circle"></i> Save & Upload</button>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
