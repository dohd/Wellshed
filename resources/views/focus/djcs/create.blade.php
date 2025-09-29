@extends ('core.layouts.app')

@section ('title', 'Create | Site Survey Report')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Site Survey Report Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.djcs.partials.djcs-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.djcs.store', 'method' => 'POST', 'files' => true ]) }}
                        @include('focus.djcs.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
@include('focus.djcs.form_js')

{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">

    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    $('#lead_id').change(function() {
        $.ajax({
            url: "{{ route('biller.djcs-default-inputs') }}",
            method: 'GET',
            data: { leadId: $("#lead_id").val() },
            dataType: 'json', // Adjust the data type accordingly
            success: data => {
                console.log(data);

                // Set content for root_cause
                var rootCauseEditor = tinymce.get('root_cause'); // Replace with the actual ID
                if (rootCauseEditor) {
                    rootCauseEditor.setContent(data.findings);
                }

                // Set content for action_taken
                var actionTakenEditor = tinymce.get('action_taken'); // Replace with the actual ID
                if (actionTakenEditor) {
                    actionTakenEditor.setContent(data.action);
                }

                // Set content for recommendations
                var recommendationsEditor = tinymce.get('recommendations'); // Replace with the actual ID
                if (recommendationsEditor) {
                    recommendationsEditor.setContent(data.recommendations);
                }
            }
        });
    });

</script>

@endsection
