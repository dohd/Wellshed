@extends ('core.layouts.app')

@section ('title', 'Create Order')

@section('page-header')
    <h1>
        <small>Create Order</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Order</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.customer_orders.partials.customer_orders-header-buttons')
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
                                    {{ Form::open(['route' => 'biller.customer_orders.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.customer_orders.form")
                                        {{-- <div class="edit-form-btn float-right">
                                            {{ link_to_route('biller.customer_orders.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                          
                                        </div><!--edit-form-btn--> --}}
                                    </div><!-- form-group -->

                                    {{ Form::close() }}
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {};
        const Index = {
            init(){
                $('#customer').select2({allowClear: true});
                let docRowId = 0;
                const docRow = $('#itemsTbl tbody tr').html();
                $('#addRow').click(function() {
                    docRowId++;
                    let html = docRow.replace(/-0/g, '-'+docRowId);
                    $('#itemsTbl tbody').append('<tr>' + html + '</tr>');
                });
                // remove schedule row
                $('#itemsTbl').on('click', '.remove_doc', function() {
                    $(this).parents('tr').remove();
                    docRowId--;
                });
            },
        };
        $(() => Index.init());
    </script>
@endsection
