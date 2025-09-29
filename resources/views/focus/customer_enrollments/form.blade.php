<div class="row">
    <div class="col-sm-6 cmp-pnl">
        <div class="inner-cmp-pnl">

            <!-- Client Type Selection -->
            <div class="form-group row">
                <div class="col-md-8">
                    <div class="m-1">
                        <label for="client-type">Select Client Type</label>
                        <div class="d-inline-block custom-control custom-radio mr-1">
                            <input type="radio" class="custom-control-input client-type" name="client_status"
                                id="colorCheck1" value="customer" checked>
                            <label class="custom-control-label" for="colorCheck1">Existing</label>
                        </div>
                        <div class="d-inline-block custom-control custom-radio mr-1">
                            <input type="radio" class="custom-control-input client-type" name="client_status"
                                id="colorCheck3" value="new">
                            <label class="custom-control-label" for="colorCheck3">New Client</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Customer Select -->
            <div class="form-group row">
                <div class="col-sm-6">
                    <label for="customer" class="caption">Customer <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select id="customer" name="client_id" class="form-control" data-placeholder="Choose Customer">
                            @foreach ($customers as $row)
                                <option value="{{ $row->id }}">{{ $row->company }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- New Client Fields -->
            <div class="form-group row">
                <div class="col-sm-6">
                    {{ Form::label('name', 'Client Name', ['class' => 'control-label']) }}
                    {{ Form::text('name', null, ['class' => 'form-control new-client-field', 'placeholder' => 'Client Name']) }}
                </div>
                <div class="col-sm-6">
                    {{ Form::label('phone', 'Client Contact', ['class' => 'control-label']) }}
                    {{ Form::text('phone', null, ['class' => 'form-control new-client-field', 'placeholder' => 'Client Contact']) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6">
                    {{ Form::label('email', 'Client Email Address', ['class' => 'control-label']) }}
                    {{ Form::text('email', null, ['class' => 'form-control new-client-field', 'placeholder' => 'Client Email Address']) }}
                </div>
                <div class="col-sm-6">
                    {{ Form::label('dob', 'D.O.B', ['class' => 'control-label']) }}
                    {{ Form::text('dob', null, ['class' => 'form-control datepicker new-client-field', 'placeholder' => 'D.O.B']) }}
                </div>
            </div>
        </div>
    </div>


    <div class="col-sm-6 cmp-pnl">
        <div class="inner-cmp-pnl">

            <div class="form-group row">
                <div class="col-4">
                    <input type="hidden" name="is_review" value="0">
                    <input type="checkbox" id="my_checkbox" name="is_review" value="1"><label for="is_review">Review</label>
                </div>
            </div>
            <!-- Redeemable Code -->
            <div class="form-group row">
                <div class="col-sm-6">
                    {{ Form::label('redeemable_code', 'Redeemable Code', ['class' => 'control-label']) }}
                    {{ Form::text('redeemable_code', null, [
                        'class' => 'form-control',
                        'id' => 'redeemable_code',
                        'placeholder' => 'Redeemable Code',
                        'required',
                        'style' => 'text-transform: uppercase;',
                    ]) }}
                    <span id="redeemable_message" style="display:none;"></span>
                </div>
            </div>

            <!-- Hidden Promo Type -->
            <input type="hidden" id="promo_type" name="promo_type" value="{{ @$promotionalCode->promo_type }}">
            <input type="hidden" id="promo_code_id" name="promo_code_id" value="{{ @$promotionalCode->promo_code_id }}">

            <!-- Promo Options -->
            <div class="form-group row">

                <!-- Product Categories -->
                <div class="form-group col-12" id="categorySelectWrapper" style="display: none;">
                    {{ Form::label('productCategories', 'Select Product Categories') }}
                    <select id="productCategories" name="product_categories[]" class="form-control select2"
                        multiple></select>
                </div>

                <!-- Products -->
                <div class="form-group col-12" id="productSelectWrapper" style="display: none;">
                    {{ Form::label('products', 'Select Products') }}
                    <select id="products" name="products[]" class="form-control select2" multiple></select>
                </div>

                <!-- Description Promo -->
                <div class="form-group col-12" id="descriptionPromoWrapper" style="display: none;">
                    {{ Form::label('descriptionPromoText', 'Describe your promo', ['class' => 'control-label']) }}
                    <textarea id="descriptionPromoText" name="description_promo" class="form-control tinyinput" rows="4"></textarea>
                </div>

            </div>

            <!-- Notes -->
            <div class="form-group row">
                <div class="col-lg-10">
                    {{ Form::label('note', 'Product / Services Required', ['class' => 'control-label']) }}
                    {{ Form::textarea('note', null, [
                        'class' => 'form-control round',
                        'rows' => '3',
                        'placeholder' => 'Product / Services Required',
                    ]) }}
                </div>
            </div>

        </div>
    </div>

</div>
