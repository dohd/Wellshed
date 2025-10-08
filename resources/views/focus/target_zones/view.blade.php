@extends ('core.layouts.app')

@section('title', 'View Target Zone')

@section('page-header')
    <h1>
        <small>View Target Zone</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Target Zone</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.target_zones.partials.target_zones-header-buttons')
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
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Name</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $target_zone['name'] }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Description</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $target_zone['description'] }}</p>
                                        </div>
                                    </div>


                                </div>

                                <div class="card-body">
                                    <table id="daysTbl" class="table" widht="50%">
                                        <thead>
                                            <tr>
                                                <th>Locations</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($target_zone->items as $item)
                                                <tr>
                                                    <td>
                                                        {{ $item->sub_zone_name }}
                                                    </td>

                                                
                                                </tr>
                                            @endforeach
                                            
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
