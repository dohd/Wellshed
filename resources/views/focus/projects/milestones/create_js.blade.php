{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}
<script>
    const config = {
        ajax: {
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}
        },
        date: {autoHide: true, format: '{{config('core.user_date_format')}}'},
    };
    // ajax header set up
    

    const Form = {
        init(){
            $.ajaxSetup(config.ajax);
            $('#budget').select2();
            $('#employee').select2();
            $('[data-toggle="datepicker"]').datepicker(config.date);
            $('.from_date').datepicker(config.date).datepicker('setDate', '{{dateFormat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d')))))}}');
            $('.to_date').datepicker(config.date).datepicker('setDate', 'today');
            $('#color').colorpicker();
            $.get("{{ route('biller.projects.budget_limit', $project) }}", ({data}) => {
                const budgetLimit = accounting.formatNumber(data.milestone_budget);
                $('.milestone-limit').text(budgetLimit);
                let limit = accounting.unformat($('.milestone-limit').text());
                if (!limit || limit < 0) $('#milestone-amount').attr('disabled', true);
                
            });

            $('#milestone-amount').change(function() {
                const milestoneBudget = accounting.unformat($('.milestone-limit').text());
                if (this.value > milestoneBudget) this.value = milestoneBudget;
                this.value = accounting.formatNumber(this.value);
            });
            $('#budget').change(Form.budgetChange);
            $('#budgetsTbl tbody').on('click','.check', Form.selectItemChange);
            $('#budgetsTbl tbody').on('change','.qty', Form.qtyChange);
            $('#submitmilestoneForm').click(function(e) {
                e.preventDefault();

                let formData = {
                    data: $('#milestoneForm').serializeArray()
                };
                console.log(formData);

                $.ajax({
                    url: "{{ route('biller.milestones.store') }}",
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    success: function(response) {
                        console.log('Form data successfully submitted', response);
                        if (response.success) {
                            // Display success message
                            $('#message').html('<div class="alert alert-success">' + response.success + '</div>');
                            window.location.href = response.redirect;
                        } else {
                            // Display error message
                            $('#message').html('<div class="alert alert-danger">' + response.error + '</div>');
                        }
                        // location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred while submitting form', error);
                    }
                });
            });
        },
        budgetChange(){
            const budget_id = $('#budget').val();
            $('#budgetsTbl tbody').html('');
            $.ajax({
                url: "{{ route('biller.milestones.get_budget_items') }}",
                method: 'POST',
                data: {
                    budget_id: budget_id
                },
                success: function(data){
                    console.log(data);
                    data.forEach((v,i) => {
                        $('#budgetsTbl tbody').append(Form.productRow(v,i));
                    });
                }
            });
        },
        productRow(v,i){
            const qty = v.new_qty - v.qty_allocated_to_milestones;
            return `
                <tr>
                    <td>${i+1}</td>
                    <td>
                        ${v.product_name}
                    </td>
                    <td>${v.unit}</td>
                    <td><span class="budget_qty">${+v.new_qty}</span></td>  
                    <td><span class="qty_allocated_to_milestones">${+v.qty_allocated_to_milestones}</span></td>  
                    <td><input type="text" class="form-control qty" name="qty[]" value="${+qty}" id="qty-p0" step="0.1" required disabled></td>
                    <td><span class="amount">0</span></td>
                    <td class="text-center">
                        <input type="checkbox" value="1" id="check" class="btn btn-sm form-control check">
                    </td>
                    <input type="hidden" name="product_id[]" value="${v.product_id}" class="product_id" id="productid-p0" disabled>
                    <input type="hidden" name="budget_item_id[]" class="id" value="${v.id}" disabled>
                    <input type="hidden" name="unit_id[]" class="unit_id" value="${v.unit_id}" disabled>
                    <input type="hidden" name="price[]" class="price" value="${v.price}" disabled>
                </tr>
        `;
        },
        selectItemChange(){
            const el = $(this);
            const row = el.parents('tr:first');
            if (el.is(':checked')) {
                row.find('.qty').prop('disabled', false);
                row.find('.product_id').prop('disabled', false);
                row.find('.id').prop('disabled', false);
                row.find('.unit_id').prop('disabled', false);
                row.find('.price').prop('disabled', false);
                let qty = row.find('.qty').val();
                let price = row.find('.price').val();
                let amount = qty*price;
                row.find('.amount').text(amount);
                Form.calcTotal();
            } else {
                row.find('.qty').prop('disabled', true);
                row.find('.product_id').prop('disabled', true);
                row.find('.id').prop('disabled', true);
                row.find('.unit_id').prop('disabled', true);
                row.find('.price').prop('disabled', true);
                row.find('.amount').text(0);
                Form.calcTotal();
            }
            // console.log(row.find('.qty').val());
        },
        qtyChange(){
            const el = $(this);
            const row = el.parents('tr:first');
            const budget_qty = accounting.unformat(row.find('.budget_qty').text());
            const qty_allocated_to_milestones = accounting.unformat(row.find('.qty_allocated_to_milestones').text());
            const qty = accounting.unformat(row.find('.qty').val());
            let sum_qty = qty + qty_allocated_to_milestones;
            if (sum_qty > budget_qty){
                let qty_diff = budget_qty - qty_allocated_to_milestones;
                row.find('.qty').val(qty_diff)
            }else if (sum_qty < budget_qty){
                row.find('.qty').val(qty)
            }
            let v_qty = row.find('.qty').val();
            let price = row.find('.price').val();
            let amount = v_qty*price;
            row.find('.amount').text(amount);
            Form.calcTotal();
            console.log(budget_qty, qty);

        },
        calcTotal() {
            let total = 0;
            $("#budgetsTbl tbody tr").each(function(i) {
                
                const amount = accounting.unformat($(this).find('.amount').text());
                total += amount;
            });
            $('#milestone-amount').val(accounting.formatNumber(total));
            
        }
    };
    $(Form.init)

  
</script>