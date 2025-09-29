@extends ('core.layouts.app')

@section ('title', trans('labels.backend.hrms.management'))

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-2">
            <div class="content-header-left col-md-6 col-12">
                <h4 class="content-header-title mb-0">{{ trans('labels.backend.access.roles.management') }}</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.hrms.partials.role-header-buttons')
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
                                {{ Form::model($role, ['route' => ['biller.role.update', $role], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-role']) }}
                                <div class="box box-info">
                                    <div class="box-body">

                                        <div class="form-group">
                                            <div class="col-lg-10">
                                                <label for="name" > Role Name </label>
                                                <input type="text" id="name col-lg-5" class="form-control" placeholder="Role Name" name="name" value="{{ $role->name }}" required>
                                            </div><!--col-lg-10-->
                                        </div><!--form control-->

                                        <div class="form-group">
                                            {{ Form::label('associated_permissions', trans('validation.attributes.backend.access.roles.associated_permissions'), ['class' => 'col-lg-2 control-label']) }}
                                            <div class="col-lg-10">
                                                {{ Form::select('associated_permissions', ['none' => trans('meta.select'), 'custom' =>  trans('hrms.permissions')], $role->all ? 'none' : 'custom', ['class' => 'form-control select2 box-size']) }}
                                                <div id="available-permissions" class="mt-2"
                                                     style="height: 1000px; overflow-x: hidden; overflow-y: scroll;">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            @if ($permissions->count())
                                                                @php
                                                                    $groupClassName =  null;
                                                                @endphp


                                                                @foreach ($permissions as $perm)

                                                                    @if( strtolower(explode(' ', $perm->display_name)[0]) !== $groupClassName )

                                                                        @php
                                                                            $groupClassName = strtolower(explode(' ', $perm->display_name)[0]);
                                                                        @endphp

                                                                        <div class="mt-2">
                                                                            <input type="checkbox"
                                                                                   id="pg-master-{{strtolower(explode(' ', $perm->display_name)[0])}}"
                                                                                   style="width: 20px; height: 20px;"
                                                                                   class="round pg-master-{{strtolower(explode(' ', $perm->display_name)[0])}}"
                                                                            >
                                                                            <label for="pg-master-{{strtolower(explode(' ', $perm->display_name)[0])}}" style="font-size: 22px;">  Grant All '{{ explode(' ', $perm->display_name)[0] }}' Permissions </label>
                                                                        </div>

                                                                    @endif


                                                                    <label class="control--checkbox">
                                                                        <input class="icheckbox_square icheckbox_flat-blue pg-child-{{strtolower(explode(' ', $perm->display_name)[0]) }}"
                                                                               type="checkbox"
                                                                               name="permissions[{{ $perm->id }}]"
                                                                               value="{{ $perm->id }}"
                                                                               id="perm_{{ $perm->id }}"
                                                                                {{ is_array(old('permissions')) ? (in_array($perm->id, old('permissions')) ? 'checked' : '') : (in_array($perm->id, $rolePermissions) ? 'checked' : '') }} />
                                                                        <label for="perm_{{ $perm->id }}">{{ $perm->display_name }}</label>
                                                                        <div class="control__indicator"></div>
                                                                    </label>
                                                                    <br/>
                                                                @endforeach
                                                            @else
                                                                <p>There are no available permissions.</p>
                                                            @endif
                                                        </div><!--col-lg-6-->
                                                    </div><!--row-->
                                                </div><!--available permissions-->
                                            </div><!--col-lg-3-->
                                        </div><!--form control-->

                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.role.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
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
    <script type="text/javascript">
        // Backend.Utils.documentReady(function () {
        //     Backend.Roles.init("edit")
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
