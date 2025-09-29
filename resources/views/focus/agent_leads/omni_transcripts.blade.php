@extends ('core.layouts.app')
@section ('title', 'AI Leads Management')

@section('content')
    <style>
        .scroll-down {
            position: absolute;
            bottom: 80px;
            right: 400px;
            font-size: 20px;
            cursor: pointer;
            display: none; /* Initially hidden */
        }
        .chat-item {
            cursor: pointer; 
            margin-bottom: 3px; 
            padding-top: 3px; 
            background: #F6F9FD; 
            border-top: 0 !important;
        }
        .chat-item .avatar {
            font-size: 50px;
            color: #96E7F1; 
        }
        .chat-item.active {
            background: #99e7cc ; 
        }
        .chat-item.active .avatar {
            color: #F6F9FD ;
        }
        .msg-count {
            border-radius: 20px; 
            width: 50px; 
            padding-top: 5px;
        }
    </style>

    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">AI Transcripts</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            <a href="{{ route('biller.agent_leads.index') }}" class="btn btn-info  btn-lighten-2">
                                <i class="fa fa-list-alt"></i> AI Leads
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="mb-2 text-center h2 font-weight-bold">Chat Transcript</h3>    
                                        <!-- Date filters -->
                                        <div class="row">
                                            <div class="col-md-3 pr-0">
                                                <div class="row no-gutters mb-1">
                                                    <div class="col-md-9 mr-1">
                                                        <div>Chats Between</div>
                                                        <div class="form-inline">
                                                            <input type="text" placeholder="{{ date('d-m-Y') }}" id="start_date" class="form-control form-control-sm col-md-5 mr-1 datepicker">
                                                            <input type="text" placeholder="{{ date('d-m-Y') }}" id="end_date" class="form-control form-control-sm col-md-5 datepicker">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-1">
                                                    <div class="col-md-8">
                                                        <select id="source-filter" class="custom-select" style="height: 2em">
                                                            <option value="">-- filter source --</option>
                                                            @foreach (['whatsapp', 'facebook', 'instagram', 'website'] as $item)
                                                                <option value="{{ $item }}">{{ $item }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="button" id="clear" value="Clear" class="btn btn-secondary btn-sm" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 
                                                                           
                                        <div class="row">
                                            <!-- Chat List -->
                                            <div class="col-md-3 pr-0">
                                                <div class="chat-item-parent border rounded p-0" style="height: 550px; overflow-y: auto; background: #F6F9FD">
                                                    <!-- Chats will be dynamically loaded here -->
                                                </div>
                                            </div>

                                            <!-- Chat Thread -->
                                            <div class="col-md-6 pr-0">
                                                <div id="chat-transcript" class="border rounded p-2" style="height: 500px; overflow-y: auto; background: #F6F9FD">
                                                  <!-- Chat messages will be dynamically loaded here -->
                                                    <div class="ml-auto mr-auto" style="width:100px"><i class="fa fa-spinner spinner fa-lg"></i></div>
                                                </div>
                                                <span class="scroll-down" id="scroll-down"><i class="fa fa-arrow-down" aria-hidden="true"></i></span>
                                                <div id="chart-reply" class="d-flex mt-1">
                                                    <textarea id="reply-text" class="form-control mr-1" cols="30" rows="1" placeholder="Type reponse..."></textarea>
                                                    <button type="button" id="submit-reply" class="btn btn-primary" disabled><i class="fa fa-paper-plane fa-lg"></i></button>
                                                </div>
                                            </div>

                                            <!-- Chat Meta  -->
                                            <div class="col-md-3">
                                                <div class="border rounded p-1" style="height: 550px; overflow-y: auto; background: #F6F9FD">
                                                    <h4 class="text-center font-weight-bold mb-3">---- User Details ----</h4>
                                                    <div class="mb-1 h5">
                                                        <span><b>Source: </b></span><span id="dtl-source"></span><br><br>
                                                        <span><b>Last Converse: </b></span><span id="dtl-timestamp"></span><br><br>
                                                        <span><b>Phone No: </b></span><span id="dtl-phone"></span><br><br>
                                                        <span><b>Country: </b></span><span id="dtl-country"></span><br>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
@include('focus.agent_leads.omni_transcripts_js')
@endsection
