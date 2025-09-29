@extends ('core.layouts.app')

@section ('title',  'View Prospect Questions')

@section('page-header')
    <h1>
        
        <small>View Prospect Questions</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Prospect Questions</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.prospect_questions.partials.prospect_questions-header-buttons')
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
                                            <p>Title</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$prospect_question['title']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Description</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$prospect_question['description']}}</p>
                                        </div>
                                    </div>


                                </div>


                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="questionTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr class="bg-gradient-directional-blue white">
                                                <th class="text-center">#No</th>
                                                <th class="text-center">Question</th>
                                                <th class="text-center">Answer Type</th>
                                            </tr>
                                    
                                        </thead>
                                        <tbody>
                                
                                            @if (isset($prospect_question))
                                                @foreach ($prospect_question->questions as $k => $item)
                                                    <tr>
                                                        <td><span>{{$k+1}}</span></td>
                                                        <td>{{$item->question}}</td>
                                                        <td>
                                                            <select name="type[]" id="type-p{{$k}}" class="form-control" disabled>
                                                                <option value="yes_no" {{$item->type == 'yes_no' ? 'selected' : ''}}>Yes/No</option>
                                                                <option value="naration" {{$item->type == 'naration' ? 'selected' : ''}}>Naration</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                    
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
