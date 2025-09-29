<!DOCTYPE html>

@include('tinymce.scripts')


<form action="{{ isset($documentManager) ? route('biller.document-tracker.update', $documentManager->id) : route('biller.document-tracker.store') }}" method="POST">

    @csrf
    @if(isset($documentManager))
        @method('PUT')
    @endif

    <div class="row mb-2">

        <div class="col-12 col-lg-6">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ isset($documentManager) ? $documentManager->name : '' }}" required>
        </div>

        <div class="col-12 col-lg-3">
            <label for="document_type">Document Type:</label>
            <select id="document_type" name="document_type" class="form-control" required>
                @foreach($documentTypes as $type)
                    <option value="{{ $type }}" {{ isset($documentManager) && $documentManager->document_type === $type ? 'selected' : '' }}>{{ ucfirst(strtolower($type)) }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-lg-3">
            <label for="status">Status:</label>
            <select id="status" name="status" class="form-control tin" required>
                <option value=""> Select a Status </option>

                @foreach($documentStatuses as $status)
                    <option value="{{ $status }}" {{ isset($documentManager) && $documentManager->status === $status ? 'selected' : '' }}>{{ ucfirst(strtolower($status)) }}</option>
                @endforeach
            </select>
        </div>

    </div>


    <div class="row mb-2">

        <div class="col-12 col-lg-8">
            <label for="description">Description:</label><br>
            <textarea id="description" name="description" class="tinyinput" rows="4">{{ isset($documentManager) ? $documentManager->description : '' }}</textarea>
        </div>

    </div>

    <div class="row mb-2">

        <div class="col-8 col-lg-4">
            <label for="responsible">Responsible Person:</label>
            <select id="responsible" name="responsible" class="form-control" required>
                <option value=""> Select an Employee </option>
                @foreach($employees as $employee)
                    <option value="{{ $employee['id'] }}" @if(isset($documentManager) && $documentManager->responsible === $employee['id']) selected @endif>{{ $employee['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-8 col-lg-4">
            <label for="co_responsible">Co-Responsible Person:</label>
            <select id="co_responsible" name="co_responsible" class="form-control">
                <option value=""> Select an Employee </option>
                @foreach($employees as $employee)
                    <option value="{{ $employee['id'] }}" {{ isset($documentManager) && $documentManager->co_responsible === $employee['id'] ? 'selected' : '' }}>{{ $employee['name'] }}</option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="row mb-2">

        <div class="col-12 col-lg-8">
            <label for="issuing_body">Issuing Body:</label>
            <input type="text" id="issuing_body" name="issuing_body" class="form-control" value="{{ isset($documentManager) ? $documentManager->issuing_body : '' }}" required>
        </div>

        <div class="col-12 col-lg-4">
            <label for="issue_date">Issue Date:</label>
            <input type="date" id="issue_date" name="issue_date" class="form-control" value="{{ isset($documentManager) ? $documentManager->issue_date : '' }}" required>
        </div>

    </div>

    <div class="row mb-2">

        <div class="col-12 col-lg-3">
            <label for="cost_of_renewal">Cost of Renewal:</label>
            <input type="number" step="0.01" id="cost_of_renewal" name="cost_of_renewal" class="form-control" step="0.01" value="{{ isset($documentManager) ? $documentManager->cost_of_renewal : '' }}" required>
        </div>

        <div class="col-12 col-lg-3">
            <label for="renewal_date">Upcoming Renewal Date:</label>
            <input type="date" id="renewal_date" name="renewal_date" class="form-control" value="{{ isset($documentManager) ? $documentManager->renewal_date : '' }}" required>
        </div>

        <div class="col-12 col-lg-3">
            <label for="expiry_date">Expiry Date:</label>
            <input type="date" id="expiry_date" name="expiry_date" class="form-control" value="{{ isset($documentManager) ? $documentManager->expiry_date : '' }}" required>
        </div>

        <div class="col-12 col-lg-3">
            <label for="alert_days_before">Alert X Days Before Renewal Date:</label>
            <input type="number" step="0.01" id="alert_days_before" name="alert_days_before" class="form-control" value="{{ isset($documentManager) ? $documentManager->alert_days_before : '21' }}" required>
        </div>


    </div>

    <div class="row mt-4">
        <div class="col-4 col-lg-3 d-flex justify-content-center">
            <button type="submit" class="form-control btn-primary text-white">{{ isset($documentManager) ? 'Update Document' : 'Create Document' }}</button>
        </div>
    </div>

</form>

@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
    {{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
    {{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
    {{ Html::script('focus/js/select2.min.js') }}
    <script>


        $("#document_type").select2();
        $("#status").select2({});
        $("#responsible").select2({});
        $("#co_responsible").select2({});


        tinymce.init({
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 300,
        });


    </script>

@endsection
