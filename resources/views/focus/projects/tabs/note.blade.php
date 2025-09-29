<div class="tab-pane" id="tab_data6" aria-labelledby="tab6" role="tabpanel">
    <button type="button" class="btn btn-info mr-2" id="addNote" data-toggle="modal" data-target="#AddNoteModal">
        <i class="fa fa-plus-circle"></i> Note
    </button>
    <ul class="timeline">
        @php
            $flag = true;
            $total = count($project->project_notes);
        @endphp
        @foreach ($project->project_notes as $row)
            <li class="{!! (!$flag)? 'timeline-inverted' : '' !!} {{ $row->user_type == 'customer' ? 'timeline-item-right' : 'timeline-item-left' }}" id="m_{{$row['id']}}">
                <div class="timeline-badge" style="background-color:@if ($row->user_type == 'customer') #000   @else #0b97f4  @endif;">
                    {{$row->user_type}}
                </div>
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <h4 class="timeline-title">{{$row['title']}}</h4>
                    </div>
                        <div class="timeline-body mb-1">
                            <p>{{strip_tags($row['content'])}}</p>
                            
                        </div>
                    <small class="text-muted"><i class="fa fa-user"></i>
                        <strong>{{ @$row->creator->fullname }}</strong><br>
                        <strong>{{ @$row->creator->role->name }}</strong><br>
                        <i class="fa fa-clock-o"></i> {{trans('general.created')}} {{dateTimeFormat($row['created_at'])}}
                    </small>
                    <div>
                        <div class="btn-group">
                            <button class="btn btn-link note-edit" obj-type="6" data-id="{{$row['id']}}" data-url="{{ route('biller.projects.edit_meta') }}">
                                <i class="ft ft-edit" style="font-size: 1.2em"></i>
                            </button>
                            <button class="btn btn-link note-del" obj-type="6" data-id="{{$row['id']}}" data-url="{{ route('biller.projects.delete_meta') }}">
                                <i class="fa fa-trash fa-lg danger"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </li>
            @php
                $flag = !$flag;
                $total--;
            @endphp
        @endforeach
        
    </ul>
    
    {{-- <ul class="timeline">
        @foreach($project->project_notes as $message)
          
            <li class="timeline-item {{ $message->user_type == 'customer' ? 'timeline-item-right' : 'timeline-item-left' }}">
                    <div class="timeline-badge" style="background-color:@if ($message->user_type == 'customer') #000   @else #0b97f4  @endif;">{{ $message->user_type }}</div>
                    <div class="timeline-panel">
                        <h5 class="timeline-title">{{ $message->title }}</h5>
                        <p>{{ $message->content }}</p>
                    </div>
                </li>
        @endforeach
    </ul> --}}
          
</div>