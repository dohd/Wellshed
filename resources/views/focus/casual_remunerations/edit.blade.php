@extends ('core.layouts.app')

@section ('title',  "Edit Casual Labourers' Remuneration")

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Edit Casual Labourers' Remuneration</h3>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.casual_remunerations.header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">                                
                            <form action="{{ isset($casualLabourersRemuneration) ? route('biller.casual_remunerations.update', $casualLabourersRemuneration->clr_number) : route('biller.casual_remunerations.store') }}" method="POST">
                            @include('focus.casual_remunerations.form')
                            <div class="edit-form-btn ml-2">
                                {{ link_to_route('biller.casual_remunerations.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md mr-1']) }}
                                {{ Form::submit(trans('buttons.general.crud.update') . ' Remuneration', ['class' => 'btn btn-primary btn-md']) }}
                            </div>   
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
