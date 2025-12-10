@extends ('core.layouts.app')
@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Recipients Notifications Settings</h4>

                </div>

            </div>
            <div class="content-body"> 
                {{ Form::open(['route' => 'biller.send_sms.store_recipents', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post','files' => true, 'id' => 'create-hrm']) }}
                    <div class="card">
                        <div class="card-body">
                            <div class="row form-group">
                                <div class="col-4">
                                    <label for="title">Title</label>
                                    {{ Form::text('title', @$sms['sender'], ['class' => 'form-control box-size', 'placeholder' => '']) }}
                                </div>
                                <div class="col-2">
                                    <label for="type">Type of Task</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="">---select type of task---</option>
                                        <option value="dispatch_notification">Dispatch Notification</option>
                                        <option value="subscription">Subscription Notification</option>
                                        
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="">Unit of Measure</label>
                                    <div class="col">
                                        <select name="uom" id="uom" class="form-control">
                                            <option value="">--select uom--</option>
                                            @foreach (['%','AMOUNT'] as $item)
                                                <option value="{{$item}}">{{$item}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label for="Target">Target</label>
                                    <input type="text" name="target" id="target" class="form-control">
                                </div>
                               
                            </div>
                            <div class="row form-group">
                                <div class="col-6 div_users">
                                    <label for="recipients">Receipients</label>
                                    <select name="recipients[]" id="users" class="form-control" data-placeholder="Search Recipents" multiple>
                                        <option value="">Search Recipients</option>
                                        @foreach ($recipients as $user)
                                            <option value="{{$user->id}}">{{$user->fullname}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 div_all_users d-none">
                                    <label for="recipients">Receipients</label>
                                    <select name="recipients[]" id="all_users" class="form-control" data-placeholder="Search All Recipents" multiple disabled>
                                        <option value="">Search All Recipients</option>
                                        @foreach ($all_users as $user)
                                            <option value="{{$user->id}}">{{$user->fullname}}-{{@$user->business->cname}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label for="">Send Email</label>
                                    <select name="email" id="email" class="form-control">
                                        <option value="">--send email--</option>
                                        @foreach (['yes','no'] as $item)
                                            <option value="{{$item}}">{{ucfirst($item)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label for="">Send SmS</label>
                                    <select name="sms" id="sms" class="form-control">
                                        <option value="">--send sms--</option>
                                        @foreach (['yes','no'] as $item)
                                            <option value="{{$item}}">{{ucfirst($item)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="edit-form-btn">
                                {{ link_to_route('biller.dashboard', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                {{ Form::submit('Create', ['class' => 'btn btn-primary btn-md']) }}
                                <div class="clearfix"></div>
                            </div>

                        </div>
                    </div>
                {{ Form::close() }}

            </div>
            <div class="content-body">
                <div class="card">
                    <div class="card-body">
                        <table id="recipientsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>UoM/Target</th>
                                    <th>Recipients</th>
                                    <th>Send SMS</th>
                                    <th>Send Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recipient_settings as $k => $item)
                                    <tr>
                                        @php
                                            $users = explode(',', $item->recipients);
                                            $list = [];
                                            foreach ($users as $user) {
                                                $hrm = \App\Models\hrm\Hrm::withoutGlobalScopes()->with('business')->find($user);
                                                if ($hrm) {
                                                    # code...
                                                    $list[] = ['name'=>$hrm->fullname .'-'.@$hrm->business->cname, 'id'=>$hrm->id];
                                                }
                                            }
                                            $types = [
                                                'dispatch_notification'     => 'Dispatch Notification',
                                                'subscription'              => 'Subscription Notification',
                                                
                                            ];

                                            $type = $types[$item->type] ?? '8pm Daily Report';

                                        @endphp
                                        <td>{{$k+1}}</td>
                                        <td>{{$item->title}}</td>
                                        <td>{{$type}}</td>
                                        <td>{{$item->uom .'/'. $item->target}}</td>
                                        <td>
                                            <ul>
                                                @foreach($list as $v)
                                                    <li data-id="{{ $v['id'] }}">{{ $v['name'] }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td>{{$item->sms}}</td>
                                        <td>
                                            {{$item->email}}
                                        </td>
                                        <td>
                                            <a href="javascript:" data-id="{{$item->id}}" data-toggle="modal" data-target="#statusModal" class="btn btn-sm btn-primary edit_btn"><i class="fa fa-pencil"></i> </a>
                                            <a href="javascript:" data-id="{{ $item->id }}" class="btn btn-sm btn-danger delete_btn">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <input type="hidden" value="{{$item->id}}" id="" class="item_id">
                                            <input type="hidden" value="{{$item->sms}}" id="" class="sms">
                                            <input type="hidden" value="{{$item->email}}" id="" class="email">
                                            <input type="hidden" value="{{$item->type}}" id="" class="type">
                                    
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @include('focus.general.modal.edit_modal')
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        };
        const Index = {
            init(){
                $.ajaxSetup(config.ajax);
                
                $('#users').select2({allowClear: true});
                $('#all_users').select2({allowClear: true});
                $('#editRecipients').select2({allowClear: true});
                $('#edit_all_users').select2({allowClear: true});
                // $('#recipientsTbl').on('click', '.edit_btn', Index.selectItem)
                $('.edit_btn').on('click', function() {
                    var $row = $(this).closest('tr');
                    var id = $(this).data('id');
                    
                    // Get the values from the row
                    var title = $row.find('td:nth-child(2)').text();
                    var type = $row.find('.type').val();
                    var item_id = $row.find('.item_id').val();
                    var uomTarget = $row.find('td:nth-child(4)').text().split('/'); // split UoM and Target
                    var recipients = [];
                    
                    var recipient_ids = [];
                    $row.find('td:nth-child(5) li').each(function() {
                        recipient_ids.push($(this).data('id')); // Collect each recipient ID
                    });
                    var sms = $row.find('.sms').val();
                    var email = $row.find('.email').val();
                    
                    // Set the values in the modal form
                    $('#editTitle').val(title);
                    $('#editType').val(type.toLowerCase());  // Assuming types are lowercased in options
                    $('#editUom').val(uomTarget[0]);  // Set the UoM
                    $('#editTarget').val(uomTarget[1]);  // Set the Target

                    // Preselect the recipients in the multiple select dropdown
                    if(type == 'tenant_subscription'){
                        $('#edit_all_users').val(recipient_ids).trigger('change');
                    }
                    $('#editRecipients').val(recipient_ids).trigger('change');   // Assuming the names match in dropdown
                    $('#item_id').val(item_id);
                    $('#editEmail').val(email);
                    $('#editSms').val(sms);
                    const specificTypes = [
                        'dispatch_notification','subscription'
                    ];

                    if (specificTypes.includes(type)) {
                        $('#editUom').attr('disabled', true);
                        $('#editTarget').attr('disabled', true);
                        $('#editRecipients').attr('disabled', false);
                        $('#edit_all_users').attr('disabled', true);
                        $('.div_edit_users').removeClass('d-none');
                        $('.div_edit_all_users').addClass('d-none');
                    } else if (type === 'tenant_subscription') {
                        $('#editUom').attr('disabled', true);
                        $('#editTarget').attr('disabled', true);
                        $('#editRecipients').attr('disabled', true);
                        $('#edit_all_users').attr('disabled', false);
                        $('.div_edit_users').addClass('d-none');
                        $('.div_edit_all_users').removeClass('d-none');
                    } else {
                        $('#editUom').attr('disabled', false);
                        $('#editTarget').attr('disabled', false);
                        $('#editRecipients').attr('disabled', false);
                        $('#edit_all_users').attr('disabled', true);
                        $('.div_edit_users').removeClass('d-none');
                        $('.div_edit_all_users').addClass('d-none');
                    }
                    // Show the modal
                    $('#editModal').modal('show');
                });

                $('.delete_btn').on('click', function() {
                    var $row = $(this).closest('tr');
                    var id = $(this).data('id');

                    // Show a confirmation dialog
                    if (confirm('Are you sure you want to delete this item?')) {
                        // If confirmed, make an AJAX request to delete the item from the server
                        $.ajax({
                            url: "{{route('biller.send_sms.delete_settings')}}", // Your API endpoint for deleting the item
                            method: 'POST',
                            data: {
                                id: id, 
                                _token: '{{ csrf_token() }}' // Ensure you include the CSRF token
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Remove the row from the DOM
                                    $row.remove();
                                    alert('Item deleted successfully.');
                                } else {
                                    alert('Error: ' + response.error);
                                }
                            },
                            error: function() {
                                alert('An error occurred while deleting the item.');
                            }
                        });
                    }
                });

                $('#type').change(function () {
                    const type = $(this).val();
                    const userTypes = [
                        'dispatch_notification','subscription'
                    ];

                    if (userTypes.includes(type)) {
                        $('#uom').prop('disabled', true);
                        $('#target').prop('disabled', true);
                        $('#users').prop('disabled', false);
                        $('#all_users').prop('disabled', true);
                        $('.div_users').removeClass('d-none');
                        $('.div_all_users').addClass('d-none');
                    } else if (type === 'tenant_subscription') {
                        $('#uom').prop('disabled', true);
                        $('#target').prop('disabled', true);
                        $('#users').prop('disabled', true);
                        $('#all_users').prop('disabled', false);
                        $('.div_users').addClass('d-none');
                        $('.div_all_users').removeClass('d-none');
                    } else {
                        $('#uom').prop('disabled', false);
                        $('#target').prop('disabled', false);
                        $('#users').prop('disabled', false);
                        $('#all_users').prop('disabled', true);
                        $('.div_users').removeClass('d-none');
                        $('.div_all_users').addClass('d-none');
                    }
                });

            
            },
            selectItem(){
                const el = $(this);
                const row = el.parents('tr:first');
                let id = row.find('.item_id').val();
                $.ajax({
                    url: "{{route('biller.send_sms.get_settings')}}",
                    method : 'POST',
                    data: {
                        id: id,
                    },
                    success: function(data){
                        console.log(data);
                        $('#title').val(data.title);
                        $('#uom').val(data.uom);
                    }
                });
                console.log();
            },
        };
        $(Index.init);
    </script>
@endsection