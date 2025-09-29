<div class="col-sm-12  cmp-pnl">
    <div id="customerpanel" class="inner-cmp-pnl">
        <div class="form-group row">
            <div class="fcol-sm-12">
                <h3 class="title pl-1">Customer Info </h3>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-6"><label for="prospect_company" class="caption">Prospect Company</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('company', null, ['class' => 'form-control round', 'placeholder' => 'Company', 'id' => 'prospect_company']) }}
                </div>
            </div>
            <div class="col-sm-6"><label for="prospect_name" class="caption">Prospect Name</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('contact_person', null, ['class' => 'form-control round', 'placeholder' => 'Name', 'id' => 'prospect_name']) }}
                </div>
            </div>
            
        </div>
        <div class="form-group row">

            <div class="col-sm-6"><label for="prospect_email" class="caption">Prospect Email</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('email', null, ['class' => 'form-control round', 'placeholder' => 'Email', 'id' => 'prospect_email']) }}
                </div>
            </div>
            <div class="col-sm-6"><label for="prospect_contact" class="caption">Prospect Contact</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('phone', null, ['class' => 'form-control round', 'placeholder' => 'Contact', 'id' => 'prospect_contact']) }}
                </div>
            </div>
        </div>
        <div class="form-group row">

            <div class="col-sm-6"><label for="region" class="caption">Prospect Region</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('region', null, ['class' => 'form-control round', 'placeholder' => 'Region', 'id' => 'region']) }}
                </div>
            </div>
            <div class="col-sm-6"><label for="industry" class="caption">Industry</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('industry', null, ['class' => 'form-control round', 'placeholder' => 'Industry', 'id' => 'industry']) }}
                </div>
            </div>
        </div>
        {{ Form::hidden('id',null, ['class' => 'form-control','id'=>'id','required']) }}
    </div>
</div>




@section('after-scripts')
    @include('focus.prospects.form_js')
@endsection
