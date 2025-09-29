@extends ('core.layouts.app')

@section ('title', trans('import.import'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('features.import') }}</h4>
        </div>
    </div>
    <div class="content-body">
        <div class="card">
            <div class="card-body">
                <div class="card-block">
                    <h4>{{ $data['title'] }}</h4><hr>
                    <p class="alert alert-light mb-2">
                        Imported data format should be as per downloaded template file. 
                        <a href="{{ route('biller.import.sample_template', $data['type']) }}" target="_blank" id="download-btn">
                            <b>{{ trans('import.download_template') }}</b> ({{ $data['title'] }}).
                        </a>
                    </p>
                    <p><b>Import File: </b>{{ $data['type'] }}.csv or {{ $data['type'] }}.xls or {{ $data['type'] }}.xlsx</p>
                    {{-- Include template import form --}}
                    @include('focus.import.partials.' . $data['type'])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script>
    window.onload = function() {
        const dataType = @json($data['type']);
        $('#download-btn').click(function () {
            if (dataType == 'equipments') {
                const anchor = document.createElement('a');
                const href = @json(route('biller.import.sample_template', 'equipment_categories'));
                setTimeout(() => {
                    $(anchor).attr({href, target: '_blank',}).get(0).click();
                }, 1000);
            }
        });

        $('#project').select2({allowClear: true});
    }
</script>
@endsection