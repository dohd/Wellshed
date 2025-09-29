<!DOCTYPE html>

@extends ('core.layouts.app')

@include('tinymce.scripts')

@section ('title', 'Stakeholder')

@section('page-header')
    <h1>
        Stakeholder
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0"> Stakeholder</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.stakeholders.header-buttons')
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


                                    <div class="form-group">

                                        <div class="container mt-4">
                                            <div class="card-header bg-primary text-white mb-1">
                                                <h1 style="color: white">Stakeholder Details</h1>
                                            </div>
                                            <form>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="id" class="form-label">ID</label>
                                                        <input type="text" id="id" class="form-control" value="{{ $stakeholder['id'] }}" readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="name" class="form-label">Name</label>
                                                        <input type="text" id="name" class="form-control" value="{{ $stakeholder['name'] }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="email" class="form-label">Email</label>
                                                        <input type="text" id="email" class="form-control" value="{{ $stakeholder['email'] }}" readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="sh_authorizer" class="form-label">Authorizer</label>
                                                        <input type="text" id="sh_authorizer" class="form-control" value="{{ $stakeholder['sh_authorizer'] }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="sh_primary_contact" class="form-label">Primary Contact</label>
                                                        <input type="text" id="sh_primary_contact" class="form-control" value="{{ $stakeholder['sh_primary_contact'] }}" readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="sh_secondary_contact" class="form-label">Secondary Contact</label>
                                                        <input type="text" id="sh_secondary_contact" class="form-control" value="{{ $stakeholder['sh_secondary_contact'] }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="sh_gender" class="form-label">Gender</label>
                                                        <input type="text" id="sh_gender" class="form-control" value="{{ $stakeholder['sh_gender'] }}" readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="sh_id_number" class="form-label">ID Number</label>
                                                        <input type="text" id="sh_id_number" class="form-control" value="{{ $stakeholder['sh_id_number'] }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="sh_company" class="form-label">Company</label>
                                                        <input type="text" id="sh_company" class="form-control" value="{{ $stakeholder['sh_company'] }}" readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="sh_designation" class="form-label">Designation</label>
                                                        <input type="text" id="sh_designation" class="form-control" value="{{ $stakeholder['sh_designation'] }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="sh_access_reason" class="form-label">Access Reason</label>
                                                    <textarea d="sh_access_reason" class="form-control" readonly>{{ $stakeholder['sh_access_reason'] }}</textarea>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="sh_access_start" class="form-label">Access Start</label>
                                                        <input type="text" id="sh_access_start" class="form-control" value="{{ $stakeholder['sh_access_start'] }}" readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="sh_access_end" class="form-label">Access End</label>
                                                        <input type="text" id="sh_access_end" class="form-control" value="{{ $stakeholder['sh_access_end'] }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="role" class="form-label">Role</label>
                                                    <input type="text" id="role" class="form-control" value="{{ $stakeholder['role']['name'] }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <h2>Permissions</h2>
                                                    <div class="row">
                                                        @php
                                                            $permissionsChunked = collect($stakeholder['permissions'])->chunk(ceil(count($stakeholder['permissions']) / 2));
                                                        @endphp
                                                        @foreach ($permissionsChunked as $chunk)
                                                            <ul class="list-group col-12 col-lg-6">
                                                                @foreach ($chunk as $permission)
                                                                    <li class="list-group-item">{{ $permission['display_name'] }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        
                                    </div><!-- form-group -->

                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
