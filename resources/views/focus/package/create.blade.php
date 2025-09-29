@extends ('core.layouts.app')

@section ('title', 'Subscription Packages')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 ">
            <h4 class="content-header-title mb-0"> New Subscription Package </h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.package.header-buttons')
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
                            {{ Form::open(['route' => 'biller.subscription-packages.store', 'method' => 'post', 'id' => 'create-subscription-package']) }}
                                <div class="box box-info">
                                    <div class="box-body">


                                        <div class="form-group">
                                            {{ Form::label('name', 'Package Name', ['class' => 'col-lg-2 control-label required']) }}
                                            <div class="col-lg-10">
                                                {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Package Name', 'required' => 'required']) }}
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-10">
                                            <label for="price">Price</label>
                                            <input type="number" step="0.01" id="price" name="price" required class="form-control box-size">
                                        </div>



                                        <div class="form-group">
                                            <div class="row">
                                                @if (count($permissions) > 0)
                                                    @php
                                                        $groupClassName = null;
                                                    @endphp

                                                    <div class="row ml-1"> <!-- Start first column -->
                                                        @foreach ($permissions as $perm)
                                                            @if(strtolower(explode(' ', $perm['display_name'])[0]) !== $groupClassName)
                                                                @php
                                                                    $groupClassName = strtolower(explode(' ', $perm['display_name'])[0]);
                                                                @endphp

                                                                <div class="col-12 col-lg-6 mt-1">
                                                                    <input type="checkbox"
                                                                           id="pg-master-{{strtolower(explode(' ', $perm['display_name'])[0])}}"
                                                                           style="width: 20px; height: 20px;"
                                                                           class="round pg-master-{{strtolower(explode(' ', $perm['display_name'])[0])}}"
                                                                    >
                                                                    <label
                                                                        for="pg-master-{{strtolower(explode(' ', $perm['display_name'])[0])}}"
                                                                        style="font-size: 22px;"
                                                                    >
                                                                        <b>{{ explode(' ', $perm['display_name'])[0] }}</b> Module
                                                                    </label>
                                                                </div>
                                                            @endif

                                                            <input class="icheckbox_square icheckbox_flat-blue pg-child-{{strtolower(explode(' ', $perm['display_name'])[0]) }}"
                                                                   type="checkbox"
                                                                   name="permissions[]"
                                                                   value="{{ $perm['id'] }}"
                                                                   id="perm_{{ $perm['id'] }}"
                                                                   hidden
                                                            />
                                                        @endforeach
                                                    </div> <!-- End first column -->
                                                @endif
                                            </div>
                                        </div>


                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.role.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div>
                                    </div><!-- /.box-body -->
                                </div><!--box-->
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('js/backend/access/roles/script.js') }}
<script type="text/javascript">
    // Backend.Utils.documentReady(function(){
    //    Backend.Roles.init("rolecreate")
    // });

    $(document).ready(function () {

        const permissionClasses = @json($permissionClassNames);

        permissionClasses.forEach(function(className, index) {

            $(".pg-master-" + className).change(function () {

                let isChecked = $(this).prop('checked');
                $(".pg-child-"+ className).prop('checked', isChecked);
            });


        });



    });


</script>
@endsection