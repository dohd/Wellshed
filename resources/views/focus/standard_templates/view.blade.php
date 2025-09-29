@extends ('core.layouts.app')

@section ('title', 'View Standard Template')

@section('page-header')
    <h1>
        <small>View Standard Template</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Standard Template</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.standard_templates.partials.standard_templates-header-buttons')
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
                                            <p>{{$standard_template['name']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Description</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$standard_template['description']}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <table id="standard_templatesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Unit</th>
                                    <th>Product Code</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($standard_template)
                                    @foreach ($standard_template->standard_template_items as $k => $item)
                                        <tr>
                                            <td><span class="numbering">{{$k+1}}</span></td>
                                            <td>{{@$item->product->name}}</td>
                                            <td>{{ @$item->unit->code }}
                                            </td> 
                                            <td><span class="code" id="code-p{{$k}}">{{@$item->product->code}}</span></td>
                                            <td>{{numberFormat($item->qty)}}</td>
                                            
                                        </tr>
                                    @endforeach
                                @endisset
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
