@extends ('core.layouts.app')

@section ('title', 'Site Survey Report Management')

@section('content')
<div class="p-2">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class=" mb-0">Site Survey Report Management</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.djcs.partials.djcs-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h5 class="card-header">
            <a href="{{ route('biller.djcs.edit', [$djc, 'page=copy']) }}" class="btn btn-warning">
                <i class="fa fa-clone" aria-hidden="true"></i> Copy
            </a>
        </h5>
        <div class="card-body">            
            <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">
                        Customer & Reference Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">
                        Equipment Maintained
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">
                        Other Details
                    </a>
                </li>
            </ul>

            <div class="tab-content px-1 pt-1" id='djc-report'>
                <div class="tab-pane active in" id="active1" aria-labelledby="customer-details" role="tabpanel">
                    <table id="customer-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <th>Client</th>
                                <td>{{ @$djc->client->company }}</td>
                            </tr>
                            <tr>
                                <th>Branch</th>
                                <td>{{ @$djc->branch->name }}</td>
                            </tr>
                            <tr>
                                <th>Region</th>
                                <td>{{ $djc->region }}</td>
                            </tr>
                            <tr>
                                <th>Attention</th>
                                <td>{{ $djc->attention }}</td>
                            </tr>
                            <tr><th></th></tr>
                            <tr>
                                <th>Report No</th>
                                <td>{{ 'DjR-'.sprintf('%04d', $djc->tid) }}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ dateFormat($djc->report_date) }}</td>
                            </tr>
                            <tr>
                                <th>Client Ref No</th>
                                <td>{{ $djc->client_ref}}</td>
                            </tr>
                            <tr>
                                <th>Prepared By</th>
                                <td>{{ $djc->prepared_by }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane" id="active3" aria-labelledby="equipment-maintained" role="tabpanel">
                    <table id="technician-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Reference</th>
                            <td>{{ $djc->subject }}</td>
                        </tr>
                        <tr>
                            <th>Technician</th>
                            <td>{{ $djc->technician }}</td>
                        </tr>
                    </table>
                    <br/>
                    <table id="items-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Tag No</th>
                            <th>Job Card</th>
                            <th>Make</th>
                            <th>Capacity</th>
                            <th>Location</th>
                            <th>Last Service Date</th>
                            <th>Next Service Date</th>
                        </tr>
                        @foreach ($djc_items as $row)
                            <tr>
                                <td>{{ $row->tag_number }}</td>
                                <td>{{ $row->joc_card }}</td>
                                <td>{{ $row->make }}</td>
                                <td>{{ $row->capacity }}</td>
                                <td>{{ $row->location }}</td>
                                <td>{{ dateFormat($row->last_service_date) }}</td>
                                <td>{{ dateFormat($row->next_service_date) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                <div class="tab-pane" id="active4" aria-labelledby="other-details" role="tabpanel">
                    <table id="others-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tr>
                            <th>Call Description</th>
                            <td>{!! $djc->lead->note !!}</td>
                        </tr>
                        <tr>
                            <th>Findings and Root Cause</th>
                            <td>{!! $djc->root_cause !!}</td>
                        </tr>
                        <tr>
                            <th>Action Taken</th>
                            <td>{!! $djc->action_taken !!}</td>
                        </tr>
                        <tr>
                            <th>Recommendation</th>
                            <td>{!! $djc->recommendations !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
