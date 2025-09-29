<div class="row mb-2">
    <div class="col-10">
        <div class="row">
            <div class="col-8">
                <label for="purchaseClass">Select Non-Project Class</label>
                <select name="purchase_class_id" class="form-control" id="purchaseClass" data-placeholder="Enter Non-Project Class">
                    <option value=""></option>
                    @foreach ($purchaseClasses as $item)
                        <option value="{{ $item->id }}" {{ @$purchaseClassBudget->purchase_class_id == $item->id? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-1">
            <div class="col-4">
                <label for="financial_year_id">Financial Year</label>
                <select class="form-control box-size mb-2" id="financial_year" name="financial_year_id" required data-placeholder="Select a Financial Year"
                        aria-label="Select Financial Year">
                    <option value=""></option>
                    @foreach ($financialYears as $fY)
                        <option value="{{ $fY['id'] }}" @if(@$purchaseClassBudget['financial_year_id'] === $fY['id']) selected @endif>
                            {{ $fY['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-4">
                <label for="classlist">Select Class/Branch/Department</label>
                <select name="classlist_id" class="form-control" id="classlist_id" data-placeholder="Enter Class or Sub-class">
                    <option value=""></option>
                    @foreach ($classLists as $item)
                        <option value="{{ $item->id }}" {{ @$purchaseClassBudget->classlist_id == $item->id? 'selected' : '' }}>
                            {{ $item->name }} {{ $item->parent_class? '('. $item->parent_class->name .')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row mb-2">
    <div class="col-8">
        <label for="description">Mandate</label>
        <textarea name="description" id="description" class="col-8 col-lg-8 tinyinput" cols="30" rows="10" placeholder="Describe the mandate of this budget. Feel free to use tables or bulleted/numbered lists."
                    aria-label="Description">
            @if(!empty(@$purchaseClassBudget)) {{@$purchaseClassBudget['description']}} @endif
        </textarea>
    </div>
</div>
<div class="row mb-2">
    <div class="budget-container col-9 row">
        @foreach(['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'] as $month)
            <div class="col-6 col-lg-3">
                <label for="{{ $month }}" class="mt-2">{{ ucfirst($month) }}</label>
                <input type="number" step="0.01" id="{{ $month }}" name="{{ $month }}" required class="month-budget form-control box-size mb-1" placeholder="Set a Budget"
                        @if(!empty(@$purchaseClassBudget)) value="{{@$purchaseClassBudget[$month]}}" @else value="0.00" @endif
                        aria-label="{{ ucfirst($month) }} Budget"
                >
            </div>
        @endforeach
        <div class="col-12 col-lg-6 mt-2">
            <p style="font-size: 16px;">Total Budget: <span id="total" style="font-size: 25px; font-weight: bold"></span></p>
            <input type="hidden" step="0.01" id="budget" name="budget" required class="form-control box-size mb-2"
                    @if(!empty(@$purchaseClassBudget)) value="{{@$purchaseClassBudget['budget']}}" @else value="0.00" @endif
            >
        </div>
    </div>
</div>

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});

    $("#financial_year").select2({allowClear: true});
    $("#purchaseClass").select2({allowClear: true});
    // $("#department_id").select2({allowClear: true});
    $("#classlist_id").select2({allowClear: true});

    $(() => {
        tinymce.init({
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 200,
            license_key: 'gpl'
        });
    });

    // Function to calculate the total budget
    function calculateTotal() {
        let total = 0;
        $('.month-budget').each(function() {
            total += accounting.unformat(this.value);
        });
        $('#total').html(accounting.formatNumber(total));
        $('#budget').val(total);
    }
    calculateTotal();
    $('.budget-container').on('keyup', '.month-budget', () => calculateTotal());
</script>
@endsection
