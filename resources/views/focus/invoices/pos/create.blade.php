@extends ('core.layouts.pos_app')

@section('title', trans('pos.sale'))

@section('content')
    <form id="data_form" class="pos-form">
        <div class="pos_panel">
            <div class="rose_wrapper" id="sales_box">
                <div class="rose_wrap_left">
                    <div class="invoice_settings">
                        <ul class="choose-section inv_config">
                            <li id="products_tab" class="active"><a href="javascript:">{{ trans('pos.inventory') }}</a>
                            </li>
                            <li id="invoice_tab">
                                <a href="javascript:">{{ trans('pos.settings') }}</a>
                            </li>
                            <li id="drafts">
                                <a href="javascript:">{{ trans('pos.on_hold') }}</a>
                            </li>
                        </ul>
                    </div>

                    {{-- search product field --}}
                    <div class="row" id="search_product">
                        <div class="col-9">
                            <fieldset class="form-group position-relative has-icon-left">
                                <input type="text" class="form-control"
                                    placeholder="{{ trans('general.search_product') }}" name="keyword" id="keyword"
                                    data-std_holder="{{ trans('general.search_product') }}"
                                    data-serial_holder="{{ trans('products.search_serial_only') }}" autocomplete="off">
                                <div class="form-control-position">
                                    <i class="fa fa-barcode info fa-2x"></i>
                                </div>

                            </fieldset>
                        </div>
                        <div class="col-3">
                            <div class="btn-group" role="group">
                                <a class="btn btn-lighten-3 btn-purple" data-toggle="modal" data-target="#pos_stock"><i
                                        class="ft-package"></i></a>
                                <a class="btn   btn-blue-grey" onclick="return changeStyle();"><i class="fa fa-eye"></i></a>

                            </div>
                        </div>
                    </div>

                    {{-- searched inventory items --}}
                    <div id="items_load">
                        <div id="items_list">
                            <div class="loaded_products" id="product_group">
                                <div class="text-center blue font-large-2" id="p_loader"><i class="fa fa-cube spinner"></i>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div id="invoice_config">
                        <div class="row m-0">
                            <div class="col-10">
                                <fieldset class="form-group position-relative has-icon-left">
                                    <input type="text" class="form-control "
                                        placeholder="{{ trans('invoices.search_client') }}" id="customer-box"
                                        name="cst">
                                    <div class="form-control-position">
                                        <i class="fa fa-user-circle  fa-2x"></i>
                                    </div>
                                </fieldset>

                            </div>
                            <div class="col-2 p-0 m-0">

                                <a class="btn  btn-lighten-2 btn-instagram round" data-toggle="modal"
                                    data-target="#addCustomer"><i class="ft-plus-circle font-medium-3"></i></a>

                            </div>
                        </div>

                        <div id="customer-box-result"></div>
                        {{ Form::hidden('customer_id', @$customer->id, ['id' => 'customer_id']) }}
                        <div id="customer" style="display: none;">
                            <div class="border p-1">
                                <div class="clientinfo">
                                    <div id="customer_name"></div>
                                </div>
                                <div class="clientinfo">
                                    <div id="customer_phone"></div>
                                </div>
                                <div id="customer_pass"></div>
                            </div>
                        </div>
                        <hr>
                        <div class="row m-0">
                            <div class="col-6">
                                <strong> {{ trans('general.extra_discount') }}</strong><input type="text"
                                    class="form-control  discVal" onkeypress="return isNumber(event)" placeholder="Value"
                                    name="discount_rate" autocomplete="off" value="0" onkeyup="billUpyog()">
                                <input type="hidden" name="after_disc" id="after_disc" value="0">
                                ( {{ config('currency.symbol') }}
                                <span id="disc_final">0</span> )
                            </div>
                            <div class="col-6">
                                <strong>{{ trans('general.shipping') }}</strong><input type="text"
                                    class="form-control shipVal" onkeypress="return isNumber(event)" placeholder="Value"
                                    name="shipping" autocomplete="off" onkeyup="billUpyog()">
                                ( {{ trans('general.tax') }} {{ config('currency.symbol') }}
                                <span id="ship_final">0</span> )
                            </div>
                            <div class="col-12 mt-2"></div>
                            <div class="col-6">{{ trans('general.payment_currency_client') }}
                                <small>{{ trans('general.based_live_market') }}</small>
                                <select name="currency" class="selectpicker form-control">
                                    <option value="0">Default</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->symbol }}
                                            - {{ $currency->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">{{ trans('general.payment_terms') }} <select name="term_id"
                                    class="selectpicker form-control">
                                    @foreach ($terms as $term)
                                        <option value="{{ $term->id }}">{{ $term->title }}</option>
                                    @endforeach
                                </select></div>
                        </div>

                        <hr>
                        <div class="col">
                            <div class="row">
                                <div class="col-sm-6"><label for="invocieno"
                                        class="caption">{{ trans('invoices.tid') }}</label>

                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                aria-hidden="true"></span>
                                        </div>
                                        {{ Form::number('tid', @$tid + 1, ['class' => 'form-control round', 'placeholder' => trans('invoices.tid')]) }}
                                    </div>
                                </div>
                                <div class="col-sm-6"><label for="invocieno"
                                        class="caption">{{ trans('general.reference') }}</label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                aria-hidden="true"></span>
                                        </div>
                                        {{ Form::text('refer', null, ['class' => 'form-control round', 'placeholder' => trans('general.reference')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-sm-6"><label for="invociedate"
                                        class="caption">{{ trans('invoices.invoice_date') }}</label>

                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-calendar4"
                                                aria-hidden="true"></span>
                                        </div>
                                        {{ Form::text('invoicedate', null, ['class' => 'form-control round required', 'placeholder' => trans('invoices.invoice_date'), 'data-toggle' => 'datepicker', 'autocomplete' => 'false']) }}
                                    </div>
                                </div>

                                <div class="col-sm-6"><label for="invocieduedate"
                                        class="caption">{{ trans('invoices.invoice_due_date') }}</label>

                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-calendar-o"
                                                aria-hidden="true"></span>
                                        </div>

                                        {{ Form::text('invoiceduedate', null, ['class' => 'form-control round required', 'placeholder' => trans('invoices.invoice_due_date'), 'data-toggle' => 'datepicker', 'autocomplete' => 'false']) }}
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="taxFormat" class="caption">{{ trans('general.tax') }}</label>
                                    <select class="form-control round" onchange="changeTaxFormat()" id="taxFormat">
                                        @php
                                            $tax_format = 'exclusive';
                                            $tax_format_id = 0;
                                            $tax_format_type = 'exclusive';
                                        @endphp

                                        @foreach ($additionals as $row)
                                            <option value="{{ +$row->value }}" {{ $row->value == 16 ? 'selected' : '' }}>
                                                {{ $row->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label for="discountFormat"
                                            class="caption">{{ trans('general.discount') }}</label>
                                        <select class="form-control round" onchange="changeDiscountFormat()"
                                            id="discountFormat">
                                            @php
                                                $discount_format = '%';
                                            @endphp
                                            @foreach ($additionals as $additional_discount)
                                                <option value="{{ +$row->value }}"
                                                    {{ $row->value == 16 ? 'selected' : '' }}>
                                                    {{ $row->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class=" row">
                                <div class="col-sm-12">
                                    <label for="toAddInfo" class="caption">{{ trans('invoices.invoice_note') }}</label>

                                    {{ Form::textarea('notes', null, ['class' => 'form-control round', 'placeholder' => trans('invoices.invoice_note'), 'rows' => '2']) }}
                                </div>
                            </div>

                        </div>
                        <div class="clear mt-2"></div>

                    </div>
                    <div id="drafts_load">

                        <table class="table" id="drafts_list">
                        </table>
                        <div class="clear"></div>
                    </div>
                </div>

                <div class="rose_wrap_right">
                    <div class="receipt_panel" id="receipt_section">

                        <div class="summary">
                            <span><i class="fa fa-file-text-o"></i> {{ trans('pos.order_panel') }}</span>
                        </div>
                        <button id="inventory_view" type="button" class="btn btn-secondary btn-lighten-2 btn-sm round "
                            onclick="return false"><i class="ft-chevrons-left"></i> {{ trans('pos.back') }}
                        </button>

                        <div class="selected_items">
                            <div>
                                <div class="p-1 text-bold-600">
                                    <div class="float-left"><i class="fa fa-user-circle"></i>
                                        {{ trans('customers.customer') }}</div>
                                    <div class="float-right">
                                        <a class="view_invoice_config badge badge-primary white "><i
                                                class="fa fa-plus-circle font-size-large"></i></a> <a
                                            class="customer_mobile_view badge badge-danger white"
                                            href="javascript:void(false);"><i
                                                class="fa fa-plus-circle font-size-large"></i></a>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="info_tab display-block">
                                    <i id="pos_customer">{{ trans('business.default') }} - {{ @$customer->name }}</i>
                                </div>
                                <div class="clear"></div>
                            </div>

                            <div>
                                <div class="p-1 text-bold-600 display-block float-left">
                                    <div class="float-left"><i class="fa fa-shopping-cart"></i>
                                        {{ trans('pos.cart_items') }}</div>
                                    <div class="float-right">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="info_tab" id="empty_cart">
                                    <i>{{ trans('pos.empty_cart') }}</i>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>

                        <div class="bottom-section font-medium-3">
                            <div class="money">
                                {{ Form::hidden('subtotal', '0', ['id' => 'subttlform']) }}
                                <div class="summary-margin">{{ trans('general.total_tax') }}:
                                    <span>{{ config('currency.symbol') }} <span id="taxr"
                                            class="lightMode">0</span></span></div>
                                <div class="summary-margin">{{ trans('general.total_discount') }}:
                                    <span>{{ config('currency.symbol') }} <span id="discs"
                                            class="lightMode">0</span></span></div>
                                <div class="summary-margin">{{ trans('general.total') }}:
                                    <span>{{ config('currency.symbol') }} <span id="bigtotal"
                                            class="lightMode">0</span></span></div>


                                <div class="clear"></div>
                            </div>

                        </div>

                        <div class="pay_section">
                            <div id="bt_section" class="form-group text-center">
                                <a href="#" class="btn  btn-pink font-medium-5" title="Hold" data-toggle="modal"
                                    data-target="#save_draft_modal"><i class="ft-watch"></i></a>
                                <a href="#" class="btn  btn-success font-medium-5" data-toggle="modal"
                                    data-target="#pos_payment"><i class="ft-credit-card inline" title="Payment"></i>
                                    {{ trans('payments.payment') }}</a>
                                <a href="#" class="view_invoice_config btn btn-info font-medium-5 "
                                    title="Settings"><i class="ft-eye inline"></i></a><a href="#"
                                    class="customer_mobile_view btn btn-info font-medium-5" title="Settings"><i
                                        class="ft-eye inline"></i></a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sm-footer">
                <a class="btn btn-primary  btn-block round" id="return_panel"
                    onclick="return false">{{ trans('pos.review_order') }}<span id="total-item"></span></a>
            </div>
        </div>

        <input type="hidden" value="new_i" id="inv_page">
        <input type="hidden" value="{{ route('biller.invoices.pos_store') }}" id="pos_action">
        <input type="hidden" value="{{ route('biller.invoices.draft_store') }}" id="pos_action_draft">
        <input type="hidden" value="search" id="billtype">
        <input type="hidden" value="0" name="counter" id="ganak">
        <input type="hidden" value="{{ $tax_format }}" name="tax_format_static" id="tax_format">
        <input type="hidden" value="{{ $tax_format_type }}" name="tax_format" id="tax_format_type">
        <input type="hidden" value="{{ $tax_format_id }}" name="tax_id" id="tax_format_id">
        <input type="hidden" value="{{ $discount_format }}" name="discount_format" id="discount_format">
        <input type="hidden" value="0" name="ship_tax" id="ship_tax">
        <input type="hidden" value="0" id="custom_discount">
        <input type="hidden" value="0" name="paid_amount" id="paid_amount">
        <input type="hidden" value="0" name="total" id="invoiceyoghtml">
        <input type="hidden" value="0" name="tax" id="tax_total">
        @include('focus.modal.pos_payment')
    </form>
    @include('focus.modal.customer')
    @include('focus.modal.pos_stock')
    @include('focus.modal.pos_print')
    @include('focus.modal.pos_register')
    @include('focus.modal.pos_close_register')
    @include('focus.modal.pos_save_draft')
@endsection

@section('extra-scripts')
@include('focus.invoices.pos.pos_js')
@endsection
