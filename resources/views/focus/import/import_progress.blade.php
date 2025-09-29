@extends ('core.layouts.app')
@section ('title', trans('import.import'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('import.import') }}</h4>
        </div>
    </div>
    <div class="content-body">
        <div class="card card-block">
            <div id="notify" class="alert" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            @if ($is_success)
                <div id="ups" class="card-body">
                    <h6>{{ trans('import.import_process_started') }}</h6><hr>
                    <div class="row ">
                        <div class="col-md-12">
                            <div class="card card-block">
                                <span id="progressbar" class="progressbar text-center success text-bold-700" style="width:100%;height:80px"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <a class="btn btn-primary btn-lg"  href="{{ url()->previous() }}">
                        {!! trans('pagination.previous') !!}
                    </a>                                           
                </div>
            @else
                <div class="card-body">
                    <h6>Import Process Failed! Incorrect file format or unrecognised template uploading</h6><hr>
                    <div class="row sameheight-container">
                        <div class="col-md-12">
                            <div class="card card-block">
                                <span id="progressbar" class="progressbar text-xs-center " style="width:100%;height:30px"></span>
                            </div>
                        </div>
                    </div>
                    <h6>Import Process Failed! Incorrect file format or unrecognised template uploading!</h6>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/loading-bar.js') }}
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    const params = @json($data);

    const progressBar = new ldBar("#progressbar");
    setInterval(() => progressBar.set(Math.floor((Math.random() * 70) + 30)), 500);
    setTimeout(ajaxCall, 2000);

    function ajaxCall() {
        $.ajax({
            url: "{{ route('biller.import.process_template') . '/' . request('type') }}",
            type: 'POST',
            data: {name: "{{ $filename }}", ...params},
            success: data => {
                $("#notify .message").html("<strong>" + data.status + "</strong>: " + data.message);
                $("#progressbar").hide();
                $("#notify").addClass("alert-info white").fadeIn();
                $("html, body").scrollTop($("body").offset().top);
            },
            error: data => {
                const {message} = data.responseJSON;
                $("#notify .message").html("<strong>" + data.statusText + "</strong>: " + message);
                $("#notify").removeClass("alert-info").addClass("alert-danger").fadeIn();
                $("html, body").scrollTop($("body").offset().top);
                $("#submit-data").show();
                $("#ups").hide();
            }
        });
    }
</script>
@endsection