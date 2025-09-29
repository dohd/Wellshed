<!DOCTYPE html>

@include('tinymce.scripts')

@extends ('core.layouts.app')

@section ('title', 'Company Notice')

@section('content')
    <div class="content-wrapper">

        @permission('create-company-notice')

            <div class="content-header row mb-1">
                <div class="content-header-left col-6">
                    <h2 class=" mb-0"> Company Notice </h2>
                </div>
                <div class="content-header-right col-6">
                    <div class="media width-250 float-right">
                        <div class="media-body media-right text-right">
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <a href="{{ route( 'biller.company-notice-board.create-welcome' ) }}" class="btn btn-facebook  btn-lighten-3 round">
                                    <i class="fa fa-plus-circle"></i> Draft Company Notice
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @endauth

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-radius: 8px;">
                        <div class="card-content">
                            <div class="card-body">


                                <form action="{{ route('biller.company-notice-board.store-notice') }}" method="POST">
                                    @csrf
                                    <div class="col-12">
                                        <h4>Draft your company notice</h4>
                                        <textarea id="message" name="message" class="tinyinput" rows="4" placeholder="Type out your company's welcome message">
                                            @if($companyNotice)
                                                {{ $companyNotice->message }}
                                            @else
                                                {{$template}}
                                            @endif
                                        </textarea>
                                    </div>

                                    <div class="col-6 col-lg-4 mt-1">
                                        <button type="submit" class="btn btn-large btn-blue mb-1">
                                            <i class="fa fa-check"></i> Update Company Notice
                                        </button>
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

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    <script>
        tinymce.init({
            selector: '.tiny-display',
            menubar: '',
            plugins: '',
            toolbar: '',
            height: 1000,
            readonly  : true,
        });

        tinymce.init({
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount fullscreen',
            toolbar: 'undo redo fullscreen | blocks fontfamily fontsize | bold italic underline strikethrough | image link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 800,
            images_upload_url: '{{ route("tiny-photo", 'notice') }}',  // Laravel route for image upload
            automatic_uploads: true,
            images_upload_base_path: '',  // The base path for image uploads
            file_picker_types: 'image',  // Limit file picker to image files only

            // File picker callback
            file_picker_callback: function (cb, value, meta) {

                console.log("UPLOOOOOOOOOOOOOOOOOOOOADING");

                {{--var input = document.createElement('input');--}}
                {{--input.setAttribute('type', 'file');--}}
                {{--input.setAttribute('accept', 'image/*');--}}

                {{--input.onchange = function () {--}}
                {{--    var file = this.files[0];--}}
                {{--    var formData = new FormData();--}}
                {{--    formData.append('file', file);--}}

                {{--    let uploadedImages = [];--}}

                {{--    $.ajax({--}}
                {{--        url: '{{ route("tiny-photo") }}',--}}
                {{--        type: 'POST',--}}
                {{--        data: formData,--}}
                {{--        processData: false,--}}
                {{--        contentType: false,--}}
                {{--        headers: {--}}
                {{--            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')--}}
                {{--        },--}}
                {{--        success: function (response) {--}}
                {{--            if (response.location) {--}}
                {{--                cb(response.location);--}}

                {{--                uploadedImages.push(response.filename);--}}
                {{--                console.table(uploadedImages);--}}
                {{--                $('#uploaded_images').val(JSON.stringify(uploadedImages));--}}
                {{--            } else {--}}
                {{--                console.log('Error: ' + response.error);--}}
                {{--            }--}}
                {{--        },--}}
                {{--        error: function (jqXHR) {--}}
                {{--            console.log('Upload failed with status: ' + jqXHR.status);--}}
                {{--        }--}}
                {{--    });--}}
                {{--};--}}

                {{--input.click();--}}
            }
        });
    </script>
@endsection
