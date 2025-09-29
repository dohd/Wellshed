<div class="modal fade" id="pos_payment" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-gradient-directional-blue white">
                <h4 class="modal-title" id="myModalLabel">{{trans('pos.payment')}} ({{config('currency.symbol')}})</h4>
                <button type="button" class="close btn-danger" data-dismiss="modal" title="{{trans('general.close')}}">
                    <span>&times;</span>
                    <span class="sr-only">{{trans('pos.payment')}}</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <div class="text-center">
                    <h1 id="mahayog">0.00</h1></div>
                <a class="payment_row_add btn btn-info btn-sm  float-right"><i class="fa fa-plus-circle"></i></a>

                <div id="amount_row">
                    <div id="payment_row" class="row payment_row">
                        <div class="col-6">
                            <div class="card-title">
                                <label for="cardNumber">{{trans('general.amount')}}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control  text-bold-600 blue-grey p_amount"
                                           name="p_amount[]" placeholder="Amount" onkeypress="return isNumber(event)"
                                           onkeyup="update_pay_pos()" inputMode="numeric">
                                    <span class="input-group-addon"><i class="icon icon-cash"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card-title">
                                <label for="cardNumber">{{trans('general.payment_method')}}</label>
                                <select class="form-control" name="p_method[]">
                                    @foreach(payment_methods() as $payment_method)
                                        <option value="{{$payment_method}}">{{$payment_method}}</option>
                                    @endforeach
                                    <option value="Card">Card</option>
                                    <option value="Wallet">Wallet {{trans('payments.wallet_balance')}}</option>
                                </select></div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group  text-bold-600 red">
                                <label for="amount">{{trans('general.balance_due')}}</label>
                                <input type="text" class="form-control red" name="amount" id="balance1"
                                    onkeypress="return isNumber(event)" value="0.00" required="">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group text-bold-600 text-g">
                                <label for="b_change">{{trans('pos.change')}}</label>
                                <input type="text" onkeypress="return isNumber(event)" class="form-control green"
                                    name="b_change" id="change_p" value="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="pmt_reference">Payment Reference</label>
                                {{ Form::text('pmt_reference', null, ['class' => 'form-control', 'id' => 'pmt_reference']) }}
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group">
                                <label for="is_claim">Claim Tax</label>
                                <select name="is_claim" id="is_claim" class="custom-select">
                                    @foreach (['no', 'yes'] as $val)
                                        <option value="{{ $val }}">
                                            {{ ucfirst($val) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                </div>

                <div class="col tax-pin-col d-none">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="tax_pin">Tax Claimer PIN</label>
                                {{ Form::text('claimer_tax_pin', null, ['class' => 'form-control', 'id' => 'tax_pin']) }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col company-col d-none">
                    <div class="form-group">
                        <label for="company">Tax Claimer Company</label>
                        {{ Form::text('claimer_company', null, ['class' => 'form-control', 'id' => 'claimer_company']) }}
                    </div>
                </div>

                <div class="col">
                    <div class="form-group">
                        <label for="account">Payment Account</label>
                        <select name="p_account" id="p_account" class="custom-select">
                            <option value="">-- select account --</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->number }} {{ $account->holder }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    {{-- income account (POS) --}}
                    {{ Form::hidden('account_id', @$pos_account->id) }}
                    <div class="col-6">
                        <button class="btn btn-primary btn-lg btn-block mb-1" type="button" id="pos_future_pay"
                                data-type="4"><i class="fa fa-arrow-circle-o-right"></i> Pay Later
                        </button>
                        {{ Form::hidden('is_pay', 1, ['id' => 'is_pay']) }}
                    </div>

                    <div class="col-6">
                        <button class="btn btn-success btn-lg btn-block mb-1" type="submit" id="pos_basic_pay"
                                data-type="4"><i class="fa fa-arrow-circle-o-right"></i> {{trans('payments.pay_now')}}
                        </button>
                    </div>
                </div>

                <div class="row" style="display:none;">
                    <div class="col-xs-12">
                        <p class="payment-errors"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>