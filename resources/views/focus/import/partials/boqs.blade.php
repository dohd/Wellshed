{{-- @if (count($data['boq_sheets']) < 13) --}}
<a href="#" class="btn btn-warning btn-sm mr-1 float-right" data-toggle="modal" data-target="#boqSheetModal">
    <i class="fa fa-plus" aria-hidden="true"></i> Add BoQSheet
</a>    
{{-- @endif --}}
{{ Form::open(['route' => ['biller.import.general', 'boqs'], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'files' => true, 'id' => 'import-data']) }}
    <input type="hidden" name="update" value="1">
    {!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}
    <div class="row form-group">
        <div class="col-4">
            <label for="type">Select New / Existing BoQ</label>
            <select name="item_type" id="type" class="form-control">
                @foreach (['new','existing'] as $item)
                    <option value="{{$item}}">{{ucfirst($item)}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row form-group div-new_boq">
        <div class="col-4">
            <label for="name">BoQ Title</label>
            {{ Form::text('name', null, ['class' => 'form-control', 'required', 'id'=>'new_boq']) }}
        </div>
        <div class="col-8">
            <h1 class="text-danger">Please limit the rows uploaded per sheet to a maximum of 500 lines
            </h1>
        </div>
    </div>
    <div class="row form-group d-none div-existing_boq">
        <div class="col-4">
            <label for="title">Search Boq</label>
            <select name="boq_id" id="boq" class="form-control" data-placeholder="Search BoQ">
                <option value="">Search BoQ</option>
                @foreach ($data['boqs'] as $boq)
                    <option value="{{$boq->id}}">{{gen4tid('BoQ-', $boq->tid)}} - {{$boq->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-4">
            <label for="">Select BoQ Sheet</label>
            <select name="boq_sheet_id" id="boq_sheet_id" class="form-control" required>
                <option value="">--select boq sheet--</option>
                @foreach ($data['boq_sheets'] as $item)
                    <option value="{{$item->id}}">{{$item->sheet_name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    {{ Form::submit(trans('import.upload_import'), ['class' => 'btn btn-primary btn-md']) }}
{{ Form::close() }}
@include('focus.import.partials.boq_sheet_modal')

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajaxSetup: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        customerUrl: "{{ route('biller.customers.select') }}",
        select2(url, callback, extraData={}) {
            return {
                allowClear: true,
                ajax: {
                    url,
                    dataType: 'json',
                    type: 'POST',
                    data: ({term}) => ({search: term, ...extraData}),
                    quietMillis: 50,
                    processResults: callback
                }
            }
        },
        customerCb(data) {
            return { results: data.map(v => ({id: v.id, text: v.name + ' - ' + v.company})) }
        },
    };

    const Form = {
        init() {
            const {ajaxSetup, customerUrl, customerCb} = config;
            $.ajaxSetup(ajaxSetup);

            $('#boq').select2({allowClear: true});
            $('#boq_sheet_id').select2({allowClear: true});
            $('#type').change(Form.typeChange);
        },
        typeChange(){
            const type = $('#type').val();
            if(type == 'existing'){
                $('.div-new_boq').addClass('d-none');
                $('.div-existing_boq').removeClass('d-none');
                $('#new_boq').val('')
                $('#new_boq').attr('required',false);
            }else if(type == 'new'){
                $('.div-existing_boq').addClass('d-none');
                $('.div-new_boq').removeClass('d-none');
                $('#boq').val('')
                $('#new_boq').attr('required',true);
            }
        }
    }

    $(() => Form.init());
</script>
@endsection