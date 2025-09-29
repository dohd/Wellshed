@extends ('core.layouts.app')

@section ('title', 'View Tender')

@section('page-header')
    <h1>
        Manage Tender
        <small>View</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Tender</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.tenders.partials.tenders-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                                    <i class="fa fa-pencil" aria-hidden="true"></i> Status
                                </a>
                                <a href="#" class="btn btn-primary btn-sm mr-1" data-toggle="modal" data-target="#followUpModal">
                                    <i class="fa fa-phone" aria-hidden="true"></i> Follow Up
                                </a>
                            </div>

                            <div class="card-content">

                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Title</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$tender['title']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Description</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$tender['description']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Customer Details</p>
                                        </div>
                                        @php
                                            $clientname = @$tender->lead->client_name ?: '';
                                            $branch = @$tender->lead->branch? $tender->lead->branch->name : '';
                                            $address = @$tender->lead->client_address ?: '';
                                            $email = @$tender->lead->client_email ?: '';
                                            $cell = @$tender->lead->client_contact ?: '';
                                            if ($tender->client) {
                                                $clientname = $tender->client->company;						
                                                $branch = $tender->branch? $tender->branch->name : '';
                                                $address = $tender->client->address;
                                                $email = $tender->client->email;
                                                $cell = $tender->client->phone;
                                            }
                                        @endphp
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <b>Client Name :</b> {{ $clientname }}<br>
                                            @if ($branch)
                                                <b>Branch :</b> {{ $branch }}<br>
                                            @endif
                                            <b>Address :</b> {{ $address }}<br>
                                            <b>Email :</b> {{ $email }}<br>
                                            <b>Cell :</b> {{ $cell }}<br>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Type of Organisation</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$tender['organization_type']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Creation Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{dateFormat($tender['date'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Submission Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{dateFormat($tender['submission_date'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Tender Stages</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ucfirst($tender['tender_stages'])}}</p>
                                        </div>
                                    </div>


                                </div>


                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Site Visit Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{dateFormat($tender['site_visit_date'])}}</p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Consultant</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$tender['consultant']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Team Members</p>
                                        </div>
                                        @php
                                            $user_name = '';
                                            $team_member_ids = array_filter(explode(',', $tender->team_member_ids)); // Remove empty values

                                            if (!empty($team_member_ids)) {
                                                foreach ($team_member_ids as $user_id) {
                                                    $user = App\Models\hrm\Hrm::find($user_id);
                                                    
                                                    if ($user) { // Ensure user exists
                                                        $user_name .= $user->fullname . ', ';
                                                    }
                                                }
                                                
                                                $user_name = trim($user_name); // Trim extra space at the end
                                            }
                                        @endphp
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$user_name}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Tender Amount</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($tender['amount'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Bid Bond Duration (Days)</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$tender['bid_bond_processed']}}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Bid Bond Amount</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($tender['bid_bond_amount'])}}</p>
                                        </div>
                                    </div>


                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <table id="tenderTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Recipient</th>
                                    <th>Date</th>
                                    <th>Next Call Date</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tender->follow_ups as $i => $item)
                                    <tr>
                                        <td>{{$i+1}}</td>
                                        <td>{{$item->recipient}}</td>
                                        <td>{{dateFormat($item->date)}}</td>
                                        <td>{{dateFormat($item->reminder_date)}}</td>
                                        <td>{{$item->remark}}</td>
                                        <input type="hidden" value="{{$item->id}}" class="id">
                                        <td>
                                            <button type="button" class="btn btn-primary edit" data-toggle="modal" data-target="#followUpEditModal"> <i class="fa fa-pencil" aria-hidden="true"></i></button>
                                            
                                                {{ Form::open(['route' => ['biller.tender.delete_follow_ups', $item->id], 'method' => 'delete']) }} 
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you Sure, want to Delete?')"> <i class="fa fa-trash" aria-hidden="true"></i></button>
                                                {{ Form::close() }}               
                                            
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.tenders.partials.status')
        @include('focus.tenders.partials.follow_ups')
        @include('focus.tenders.partials.edit_follow_ups')
    </div>
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        };
        const Index = {
            init(){
                $.ajaxSetup(config.ajax);
                $('#users').select2({allowClear: true});
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#tenderTbl').on('click','.edit', Index.editCLick)
                $('#status').change(Index.statusChange);
            },

            statusChange(){
                const status = $(this).val();
                if(status == 'won'){
                    $('.div_won').removeClass('d-none');
                }else{
                    $('.div_won').addClass('d-none');
                    $('#users').val('');
                }
            },
            editCLick(){
                const el = $(this);
                const row = el.parents('tr:first');
                let id = row.find('.id').val();
                $.ajax({
                    url: "{{route('biller.tender.get_follow_ups')}}",
                    method: "POST",
                    data: {
                        id: id
                    },
                    success: function(data){
                        $('#follow_up_id').val(data.id);
                        $('#recipient').val(data.recipient);
                        $('#follow_up_date').datepicker('setDate', new Date(data.date));
                        $('#follow_up_reminder_date').datepicker('setDate', new Date(data.reminder_date));
                        $('#remark').val(data.remark);
                    }
                });
            }
        };
        $(()=>Index.init());
    </script>
@endsection