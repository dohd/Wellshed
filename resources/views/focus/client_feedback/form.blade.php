<div class="container mt-5">
    <div class="card shadow">

        <div class="card-body">

            @if(!@$clientFeedback)
                <input type="hidden" name="company_id" value="{{$company->company_id}}">
            @endif
            <!-- Personal Details (Optional) -->
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Enter your name" value="{{ @$clientFeedback->name ?? @$reservation->name }}" @if(@$clientFeedback) readonly @endif>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" value="{{ @$clientFeedback->email ?? @$reservation->email }}" @if(@$clientFeedback) readonly @endif>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter your phone" value="{{ @$clientFeedback->phone ?? @$reservation->phone }}" @if(@$clientFeedback) readonly @endif>
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Redeemable Code</label>
                <input type="hidden" name="redeemable_uuid" value="{{ @$reservation->uuid }}"  id="">
                <input type="hidden" name="promo_code_id" value="{{ @$reservation->promoCode->id }}"  id="">
                <input type="text" class="form-control" value="{{ @$clientFeedback->redeemableCode ?? @$reservation->redeemable_code }}" readonly>
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Promo Code</label>
                <input type="text" class="form-control" placeholder="" value="{{ @$clientFeedback->promo_code ?? @$reservation->promoCode->code }}" readonly>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Title (Heading)</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="Enter your title" value="{{ @$clientFeedback->title }}" @if(@$clientFeedback) readonly @endif>
            </div>

            <!-- Category Selection -->
            <div class="mb-3">
                <label for="category" class="form-label">Select Feedback Category</label>
                <select name="category" id="category"
                        class="form-control"
                        required
                        @if(@$clientFeedback) readonly @endif
                >
                    <option value="">Choose...</option>
                    <option value="Customer Direct Message" @if(@$clientFeedback->category === 'Customer Direct Message') selected @endif>Redeem code /Others</option>
                    <option value="Quality Concern" @if(@$clientFeedback->category === 'Quality Concern') selected @endif>Quality Concern</option>
                    <option value="Complaint"  @if(@$clientFeedback->category === 'Complaint') selected @endif>Complaint</option>
                </select>
            </div>

            <!-- Feedback Details -->
            <div class="mb-3">
                <label for="details" class="form-label">Feedback Details</label>
                <textarea name="details" id="details" rows="4"
                          @if(!@$clientFeedback) class="form-control tinyinput" @else class="form-control tinyinput-readonly" @endif
                          placeholder="Describe your feedback"> {{ @$clientFeedback->details }} </textarea>
            </div>

            <!-- File Upload -->
            <div class="mb-3">
                @if(!@$clientFeedback)
                    <label class="form-label">Attach Files</label>

                    <div id="file-inputs"></div>

                    <button type="button" id="add-file" class="btn btn-outline-primary btn-sm mt-2">
                        + Add another file
                    </button>

                    <small class="text-muted d-block mt-2">
                        You can add multiple attachments by clicking “Add another file”.
                    </small>
                @elseif(@$clientFeedback && @$clientFeedback->file_path)
                    @php
                        
                        $files = $clientFeedback->file_paths; // thanks to the accessor
                    @endphp

                    @if(count($files))
                    <div class="row">
                        @foreach($files as $i => $path)
                        @php
                            $url = Illuminate\Support\Facades\Storage::disk('public')->url($path); // if disk=public
                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
                            $isPdf   = $ext === 'pdf';
                        @endphp

                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-2 h-100">
                            <div class="mb-2 text-truncate" title="{{ basename($path) }}">
                                <i class="fa fa-paperclip"></i> {{ basename($path) }}
                            </div>

                            <div class="mb-2" style="min-height: 120px">
                                @if($isImage)
                                <img src="{{ $url }}" class="img-fluid rounded" style="max-height:160px;object-fit:cover;">
                                @elseif($isPdf)
                                <embed src="{{ $url }}" type="application/pdf" style="width:100%;height:160px;">
                                @else
                                <span class="text-muted small">No preview available</span>
                                @endif
                            </div>

                            <div class="d-flex gap-2">
                                {{-- Secure download via controller (index is key!) --}}
                                <a class="btn btn-sm btn-primary"
                                href="{{ route('biller.download-feedback-file', ['id' => $clientFeedback->id, 'index' => $i]) }}">
                                Download
                                </a>

                                {{-- Direct open (only if disk is public) --}}
                                <a class="btn btn-sm btn-outline-secondary" href="{{ $url }}" target="_blank">Open</a>
                            </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-muted">No attachments.</div>
                    @endif

                @endif
            </div>


        </div>
    </div>
</div>

@section('extra-scripts')

    {{ Html::script('focus/js/select2.min.js') }}
    <script>

        $(document).ready(function () {

            tinymce.init({
                selector: '.tinyinput',
                menubar: false,
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
                height: 360,
            });

            tinymce.init({
                selector: '.tinyinput-readonly',
                menubar: '',
                plugins: '',
                toolbar: '',
                height: 300,
                readonly: true,
            });

        });


    </script>
    <script>
    $(document).ready(function () {
        // --- your existing TinyMCE init here ---

        // === Dynamic file inputs ===
        const container = $('#file-inputs');
        const maxFiles = 10; // optional cap

        function addFileInput() {
            const count = container.children('.file-row').length;
            if (count >= maxFiles) {
                alert('You have reached the maximum number of files.');
                return;
            }
            const row = $(`
                <div class="input-group mb-2 file-row">
                    <input type="file" name="files[]" class="form-control" />
                    <button type="button" class="btn btn-outline-danger remove-file">Remove</button>
                </div>
            `);
            container.append(row);
        }

        // Start with one input
        addFileInput();

        // Add new input on click
        $('#add-file').on('click', addFileInput);

        // Remove a specific input
        container.on('click', '.remove-file', function () {
            $(this).closest('.file-row').remove();
        });
    });
    </script>

@endsection
