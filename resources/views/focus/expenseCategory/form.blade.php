
<div>

    <div class="row mb-2">
        <div class="col-12 col-lg-8">
            <label for="name" class="mt-2">Name</label>
            <input type="text" id="name" name="name" required class="form-control box-size mb-2">
        </div>

        <div class="col-12 col-lg-8">
            <label for="description" class="mt-2">Description</label>
            <textarea id="description" name="description" required class="form-control box-size mb-2"></textarea>
        </div>


    </div>

</div>

@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>

        $(document).ready(function () {

        });
    </script>
@endsection
