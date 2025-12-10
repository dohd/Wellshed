<div class='form-group row'>
    <div class='col-3'>
        {{ Form::label( 'name', 'Occasion',['class' => 'control-label']) }}
        <select name="type" id="type" class="form-control">
            <option value="subscription_expired" {{@$message_template->type == 'subscription_expired' ? 'selected' : ''}}>Subscription Exipired</option>
            <option value="subscription_expiring" {{@$message_template->type == 'subscription_expiring' ? 'selected' : ''}}>Subscription Expiring in 7 days</option>
        </select>
    </div>
    <div class='col-3'>
        {{ Form::label( 'name', 'Type of User',['class' => 'control-label']) }}
        <select name="user_type" id="user_type" class="form-control">
            <option value="">--select user type--</option>
            @foreach (['employee','customer','supplier'] as $item)
                <option value="{{$item}}" {{@$message_template->user_type == $item ? 'selected' : ''}}>{{ucfirst($item)}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label for="template">Search Template</label>
        <select name="template_id" id="mediaBlocksSelect" class="form-control"></select>
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'text_message', 'Message',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::textarea('text_message', null, ['class' => 'form-control round text_message', 'placeholder' => trans('departments.note')]) }}
    </div>
</div>

