<div class="tab-pane" id="tab_data5" aria-labelledby="tab5" role="tabpanel">
   
    <ul class="timeline">
        @php
            $flag = true;
            $total = count($project->milestones);
        @endphp
        @foreach ($project->milestones as $row)
            <li class="{!! (!$flag)? 'timeline-inverted' : '' !!}" id="m_{{$row['id']}}">
                <div class="timeline-badge" style="background-color:@if ($row['color']) {{$row['color']}} @else #0b97f4  @endif;">
                    {{$total}}
                </div>
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <h4 class="timeline-title">{{$row['name']}}</h4>
                        <p>
                            <small class="text-muted">
                                [{{trans('general.due_date')}} {{dateTimeFormat($row['due_date'])}}]
                            </small>
                        </p>
                    </div>
                    {{-- @if (project_access($project->id)) --}}
                        <div class="timeline-body mb-1">
                            <p>{{$row['note']}}</p>
                            <p>Amount: <b>{{ amountFormat($row['amount']) }}</b></p>
                            <p>Balance: <b>{{ amountFormat($row['balance']) }}</b></p>
                        </div>
                    {{-- @endif --}}
                    <small class="text-muted"><i class="fa fa-user"></i>
                        <strong>{{ @$row->creator->fullname }}</strong>
                        <i class="fa fa-clock-o"></i> {{trans('general.created')}} {{dateTimeFormat($row['created_at'])}}
                    </small>
                    {{-- <div>
                        <div class="btn-group">
                            <button class="btn btn-link milestone-edit" obj-type="2" data-id="{{$row['id']}}" data-url="{{ route('biller.projects.edit_meta') }}">
                                <i class="ft ft-edit" style="font-size: 1.2em"></i>
                            </button>
                            <button class="btn btn-link milestone-del" obj-type="2" data-id="{{$row['id']}}" data-url="{{ route('biller.projects.delete_meta') }}">
                                <i class="fa fa-trash fa-lg danger"></i>
                            </button>
                        </div>
                    </div> --}}
                </div>
            </li>
            @php
                $flag = !$flag;
                $total--;
            @endphp
        @endforeach
    </ul>
</div>