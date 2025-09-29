
{{ Form::open(['route' => ['biller.import.general', 'material_take_off'], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'files' => true, 'id' => 'import-data']) }}
    <input type="hidden" name="update" value="1">
    {!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}
    <div class="row form-group">
        <div class="col-4">
            <label for="type">Select QT/Budget</label>
            <select name="item_type" id="type" class="form-control">
                @foreach (['quote','budget'] as $item)
                    <option value="{{$item}}">{{ucfirst($item)}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row form-group d-none div-budget">
        <div class="col-4">
            <label for="name">Search Budget</label>
            <select name="budget_id" id="budget" class="form-control" data-placeholder="Search Budget">
                <option value="">Search Budget</option>
                @foreach ($data['budgets'] as $budget)
                @if ($budget->quote)
                    
                <option value="{{$budget->id}}">{{gen4tid('QT-', @$budget->quote->tid)}} - {{@$budget->quote->notes}}</option>
                @endif
                @endforeach
            </select>
        </div>
    </div>
    <div class="row form-group div-quote">
        <div class="col-4">
            <label for="title">Search Quote</label>
            <select name="quote_id" id="quote" class="form-control" data-placeholder="Search Quote">
                <option value="">Search Quote</option>
                @foreach ($data['quotes'] as $quote)
                    <option value="{{$quote->id}}">{{gen4tid('QT-', $quote->tid)}} - {{$quote->notes}}</option>
                @endforeach
            </select>
        </div>
    </div>
    {{ Form::submit(trans('import.upload_import'), ['class' => 'btn btn-primary btn-md']) }}
{{ Form::close() }}

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

            $('#budget').select2({allowClear: true});
            $('#quote').select2({allowClear: true});
            $('#type').change(Form.typeChange);
        },
        typeChange(){
            const type = $('#type').val();
            if(type == 'quote'){
                $('.div-quote').removeClass('d-none');
                $('.div-budget').addClass('d-none');
                $('#budget').val('');
            }else if(type == 'budget'){
                $('.div-quote').addClass('d-none');
                $('.div-budget').removeClass('d-none');
                $('#quote').val('');
            }
        }
    }

    $(() => Form.init());
</script>
@endsection