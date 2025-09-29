<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>


<div class="row mb-2">


    <div class="col-10 col-lg-3">
        <label for="name" class="mt-2">Employee</label>
        <input type="text" id="name" name="name" value="{{ $employee['details']['first_name'] . ' ' . $employee['details']['last_name'] }}" readonly class="form-control box-size mb-2">
        <input type="text" id="employeeId" name="employeeId" value="{{ $employee['details']['id'] }}" readonly hidden="" class="form-control box-size mb-2">
    </div>

    <div class="col-10 col-lg-3">
        <label for="department" class="mt-2">Department</label>
        <input type="text" id="department" name="department" value="{{ $employee['department'] }}" readonly class="form-control box-size mb-2">
    </div>
    <div class="col-10 col-lg-3">
        <label for="customers" class="mt-2">Search Customer</label>
        <select class="form-control select2" name="customer_id" id="customerFilter" data-placeholder="Search Customer">
            <option value=""></option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" {{$customer->id == @$edlSubcategoryAllocation->customer_id ? 'selected' : ''}}>{{ $customer->company }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-10 col-lg-3">
        <label for="branch" class="mt-2">Search Branch</label>
        <select class="form-control select2" id="branchFilter" name="branch_id" data-placeholder="Search Branch">
            <option value=""></option>
        </select>
    </div>

</div>

<div class="mb-4">
    @php
        $i = 1;
    @endphp

@foreach($departments as $dept)

        <h3 class="font-weight-bolder mb-2 mt-2">{{ $dept['name'] }}</h3>

        @if(!empty($deptEdlSubcategories[$dept['name']]))
            <div class="mb-1">
                <input type="checkbox"
                       id="{{$dept['id']}}master"
                       style="width: 16px; height: 16px;"
                       class="round {{$dept['id']}}master"
                >
                <label for="{{$dept['id']}}master"> Allocate All '{{ $dept['name'] }}' Key Performance Indicators </label>
            </div>


            <div class="row">
                @foreach($deptEdlSubcategories[$dept['name']] as $deptSubcat)

                    <div class="col-10 col-lg-6 custom-control custom-checkbox mb-1">
                        <input type="checkbox"
                               id="{{ $deptSubcat['id'] }}"
                               name="{{ $deptSubcat['id'] }}"
                               value="{{ $deptSubcat['id'] }}"
                               style="width: 16px; height: 16px;"
                               class="round {{$dept['id']}}child"
                               @if(in_array($deptSubcat['id'], $allocations)) checked @endif
                        >
                        <label for="{{ $deptSubcat['id'] }}"> {{ $deptSubcat['name'] }} </label>
                    </div>

                    @php
                        $i++;
                    @endphp
                @endforeach
            </div>
        @else

            <div class="ml-2">
                <p>No Categories Created for the {{ $dept['name'] }} Department</p>
                <div class="media">
                    <a href="{{ route('biller.employee-task-subcategories.create') }}" class="btn btn-dropbox round"> Create</a>
                </div>
            </div>

        @endif

    @endforeach
</div>

<script>

    $(document).ready(function () {

        const departments = @json($departments);

        departments.forEach(function(dept, index) {

            $('.' + dept.id + 'master').change(function () {
                var isChecked = $(this).prop('checked');
                $('.' + dept.id + 'child').prop('checked', isChecked);
            });


        });



    });

</script>
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        branchSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customerFilter").val()}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        }
    };

    const Index = {
        startDate: '',
        endDate: '',
        
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            
            $("#customerFilter").select2({allowClear: true}).change(Index.onChangeCustomer);
            $("#branchFilter").select2(config.branchSelect).change(Index.onChangeBranch);

            function loadBranch(branch_id) {
                $.ajax({
                    url: "{{ route('biller.branches.select') }}", // Define this route to fetch a single branch
                    type: "POST",
                    data: { customer_id: $("#customerFilter").val() },
                    dataType: "json",
                    success: function (data) {
                        if (data) {
                            let option = new Option(data.name, data.id, true, true);
                            console.log(data)
                            $('#branchFilter').append(option).trigger('change');
                        }
                    }
                });
            }

            // Assuming branch_id is stored in a hidden field
            let existingBranchId = @json(@$edlSubcategoryAllocation->branch_id);
            if (existingBranchId) {
                loadBranch(existingBranchId);
            }
        },

        onChangeCustomer() {
            $("#branchFilter option:not(:eq(0))").remove();
            // $('#projectsTbl').DataTable().destroy();
        },

        onChangeBranch() {
            // $('#projectsTbl').DataTable().destroy();
            // Index.drawDataTable(); 
        },
        
    };

    $(Index.init);
</script>

