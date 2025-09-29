

<div>

    <div class="row mb-1">
        <div class="col-12 col-lg-8">
            <label for="name" class="mt-2">Content</label>
            <textarea id="content" name="content" rows="4" required class="form-control box-size mb-2"></textarea>
        </div>

    </div>

    <div class="row mb-2">
        <!-- Start Datetime Input -->
        <div class="col-12 col-lg-4">
            <label for="start" class="form-label">Start Date & Time</label>
            <input type="datetime-local" class="form-control" id="start" name="start" required>
        </div>

        <!-- End Datetime Input -->
        <div class="col-12 col-lg-4">
            <label for="end" class="form-label">End Date & Time</label>
            <input type="datetime-local" class="form-control" id="end" name="end" required>
        </div>

        @php
            $isAdmin = Auth::user()->ins === 2;
        @endphp

        @if($isAdmin)
            <div class="col-12 col-lg-8 mt-1">
                <label for="business" class="caption" style="display: inline-block;"> Business </label>
                <select id="business" name="business[]" class="custom-select round" data-placeholder="Select a Business" multiple>
                    <option value=""></option>
                    @foreach ($businesses as $biz)
                        <option value="{{ $biz->id }}"
                                @if(@$marquee->business === $biz->id) selected @endif
                        >
                            {{ $biz->cname }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif


    </div>

</div>

{{--@section('extra-scripts')--}}
{{--    {{ Html::script('focus/js/select2.min.js') }}--}}
{{--    <script>--}}


{{--    </script>--}}
{{--@endsection--}}
