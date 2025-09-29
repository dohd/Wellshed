<div class='form-group'>
    {{ Form::label( 'title', trans('productcategories.title'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('title', null, ['class' => 'form-control box-size', 'placeholder' => trans('productcategories.title').'*','required'=>'required']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'extra', trans('productcategories.extra'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('extra', null, ['class' => 'form-control box-size', 'placeholder' => trans('productcategories.extra')]) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'code_initials', 'Product Code Initials',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{-- {{ Form::text('code_initials', null, ['class' => 'form-control box-size', 'placeholder' => 'eg. SC', 'id'=>'code_initials']) }} --}}
        <input type="text" maxlength="2" value="{{@$productcategories->code_initials}}" name="code_initials" id="code_initials" class="form-control box-size" required>
        <p class="code"></p>
    </div>
</div>
@if(!isset($productcategories->id))
    <div class='form-group'>
        {{ Form::label( 'c_type', trans('productcategories.c_type'),['class' => 'col-lg-2 control-label']) }}
        <div class='col-lg-10'>
            <select class="form-control" name="c_type" id="c_type">
                @if(@$productcategory->c_type===0)
                    <option value="0" selected>-Category-</option> @endif
                <option value="0">Category</option>
                <option value="1">Sub Category</option>
                <option value="2">Sub sub Category</option>

            </select>

        </div>
    </div>

    <div class='form-group' id="child" style="display: none">
        {{ Form::label( 'rel_id', trans('productcategories.rel_id'),['class' => 'col-lg-2 control-label']) }}
        <div class='col-lg-10'>
            <select class="form-control" name="rel_id" id="product_cat">
                <option value="0">--{{trans('productcategories.rel_id')}}--</option>
                @foreach($product_category as $item)

                    <option value="{{$item->id}}" {{ $item->id === @$products->productcategory_id ? " selected" : "" }}>{{$item->title}}</option>

                @endforeach

            </select>
        </div>
    </div>
    <div class='form-group' id="grand_child" style="display: none">
        {{ Form::label( 'child_id', 'child category',['class' => 'col-lg-2 control-label']) }}
        <div class='col-lg-10'>
            <select class="form-control" name="child_id" id="child_cat">
                <option value="0">--{{'child category'}}--</option>
            
                @foreach($product_cat as $item)

                    <option value="{{$item->id}}" {{ $item->id === @$products->productcategory_id ? " selected" : "" }}>{{$item->title}}</option>

                @endforeach

            </select>
        </div>
    </div>
@endif
@section("after-scripts")
    <script type="text/javascript">
        $(document).ready(function () {
            const config = {
                ajax: {
                    headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}
                },
                date: {autoHide: true, format: '{{config('core.user_date_format')}}'},
            };
            // ajax header set up
            $.ajaxSetup(config.ajax);
            $("#c_type").on('change', function () {
                var parent = $('#c_type :selected').val();
                if (parent == 0) {
                    $('#child').hide();
                    $('#grand_child').hide();
                    $('#child_cat').val(0);
                } else if(parent == 2) {
                    $('#product_cat').val(0);
                    $('#child').hide();
                    $('#grand_child').show();
                }
                else {
                    $('#product_cat').val(0);
                    $('#child').show();
                    $('#grand_child').hide();
                    $('#child_cat').val(0);
                }
            });

            $('#code_initials').on('keyup', function(e){
                let code = $(this).val();
                // console.log(code, code.length);
                $('.code').text('');
                $.ajax({
                    url: "/productcategories/search_code/"+code,
                    method: 'GET',
                    // data: {
                    //     code: code,
                    // },
                    success: function(response) {
                        // console.log(response);
                        if (response.exists == true) {
                            // console.log(response);
                            $('.code').text('Value exists in the database').addClass('text-danger');
                        } else {
                            $('.code').text('Value does not exist').removeClass('text-danger');
                            $('.code').text('Value does not exist').addClass('text-success');
                        }
                        // do something with the response data
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(errorThrown);
                        // handle the error case
                    }
                    });
            });
        });
    </script>
@endsection
