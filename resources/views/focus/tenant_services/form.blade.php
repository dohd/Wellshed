<div class="card rounded mb-1">
    <div class="card-content">
        <div class="card-body">     
            <div class="row">
                <div class="col-md-6 col-12">

                    <label for="package_number">Package</label>
                    <select name="package_number" id="package_number" class="form-control" data-placeholder="Select a Package">
                        <option value="">Select a Package</option>
                        @foreach ($packages as $pkg)
                            <option value="{{ $pkg->package_number }}"
                                    @if(@$tenant_service)
                                        {{ $pkg->package_number === optional(optional($tenant_service->package)->first())->package_number ? 'selected' : '' }}
                                    @endif
                            >
                                {{ $pkg->name }}
                            </option>
                        @endforeach
                    </select>

                </div>
                <div class="col-md-3 col-12">
                    <div class='form-group'>
                        {{ Form::label('cost', 'Package Cost', ['class' => 'col control-label']) }}
                        <div class='col'>
                            {{ Form::text('cost', null, ['class' => 'form-control box-size', 'placeholder' => 'Package Cost', 'id' => 'cost', 'required' => 'required', 'readonly' => 'readonly']) }}
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-12">
                    <div class="form-group">
                        <label for="maintenance_cost" class="col control-label">Maintenance Cost</label>
                        <div class="col">
                            <input
                                    type="text"
                                    name="maintenance_cost"
                                    id="maintenance_cost"
                                    class="form-control box-size"
                                    placeholder="Maintenance Cost"
                                    required
                                    value="{{ old('maintenance_cost', @$tenant_service->maintenance_cost ?? '0.00') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>  

{{-- select modules --}}
<div class="card rounded">
    <div class="card-content">
        <div class="card-body">
            <h5 class="ml-1">Active Modules</h5>

            <div id="package-modules" class="mt-1 mx-3"></div>

            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="ml-2 font-weight-bold">Total Cost: <span class="total-cost"></span></h5>
                    {{ Form::hidden('total_cost', null, ['id' => 'total-cost']) }}
                    {{ Form::hidden('extras_total', null, ['id' => 'extras-cost']) }}
                </div>
            </div>
        </div>
    </div>
</div>  


@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $('#extrasTbl tbody td').css({paddingLeft: '5px', paddingRight: '5px', paddingBottom: 0});

    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    $('#package_number').select2({allowClear: true});

    const packages = @json($packages);

    const packageChange = () => {

        // Get the selected package number
        let selectedPackageNumber = $('#package_number').val();

        // Get the div where modules will be displayed
        let modulesDiv = $('#package-modules');

        // Clear the previous modules
        modulesDiv.empty();

        // If no package is selected (i.e., input is cleared), return early and leave the div empty
        if (!selectedPackageNumber) {
            return;
        }

        // Find the selected package index
        let selectedPackageIndex = packages.findIndex((pkg) => pkg.package_number === selectedPackageNumber);

        // Get the modules of the selected package
        let modules = packages[selectedPackageIndex].modules;

        $('#cost').val(packages[selectedPackageIndex].price);

        const pkgCost = packages[selectedPackageIndex].price;
        const maintCost = accounting.unformat($('#maintenance_cost').val());

        $('.total-cost').text(pkgCost + maintCost);
        $('#total-cost').val(pkgCost + maintCost);


        // If modules exist, display them
        if (modules && modules.length > 0) {
            // Create a div for the modules and apply the Bootstrap grid classes
            let columnDiv = `<div class="row">`;

            modules.forEach(function (module) {
                columnDiv += `<div class="col-6 col-lg-3 mb-1"><li> <i class="fa fa-cube"> </i> <span style="font-size: 16px;">${module}</span></li></div>`;
            });

            columnDiv += `</div>`;

            // Append the columns to the modulesDiv
            modulesDiv.append(columnDiv);
        }

    }

    $(document).ready(function () {

        packageChange();

        $('#package_number').on('change', function () {

            packageChange();
        });
    });




    $.ajaxSetup(config.ajax);
    $('form').on('keyup', '#cost, #maintenance_cost', function() {
        calcTotals();
    });
    $('#extrasTbl').on('change', '.select', function() {
        calcTotals();
    });

    function calcTotals() {
        const pkgCost = accounting.unformat($('#cost').val()); 
        const maintCost = accounting.unformat($('#maintenance_cost').val()); 
        let extraCost = 0;
        let lineMaintCost = 0;
        $('#extrasTbl .select').each(function() {
            const row = $(this).parents('tr');
            if ($(this).prop('checked')) {
                extraCost += accounting.unformat(row.find('.extra-cost').val()); 
                extraCost += accounting.unformat(row.find('.maint-cost').val()); 
            }
        });
        const total = pkgCost + maintCost + extraCost + lineMaintCost;
        $('.total-cost').text(accounting.formatNumber(total));
        $('#total-cost').val(accounting.formatNumber(total));
        $('#extras-cost').val(accounting.formatNumber(extraCost));
    }
    
    $('form').submit(function(e) {
        $('#extrasTbl .select').each(function() {
            const row = $(this).parents('tr');
            if (!$(this).prop('checked')) row.remove();
        });
    });

    const service = @json(@$tenant_service);
    if (service && service.id) {
        $('#cost').keyup();
        const module_ids = service.module_id? service.module_id.split(',') : [];
        $('#modulesTbl .select').each(function() {
            const id = $(this).attr('id').split('-')[1];
            if (module_ids.includes(id+'')) $(this).prop('checked', true);
        });
    }
</script>
@endsection