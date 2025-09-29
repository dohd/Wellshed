@extends ('core.layouts.app')
@section ('title', trans('general.notifications'))
@section('content')
    <div class="app-content m-1">
        <div class="content-wrapper">

            <div class="content-body">
                <section class="card section-click">
                    <li class="dropdown-menu-header">
                        <h4 class="m-1"><span
                                    class="grey darken-2">{{ trans('general.notifications')}}</span><span
                                    class="notification-tag badge badge-danger float-right m-0">{{ auth()->user()->unreadNotifications->count() }}</span>
                        </h4>
                    </li>
                    @foreach(auth()->user()->unreadNotifications as $notification)
                        <li class="media-list  border-bottom-purple bg-lighten-5 bg-danger"><a href="javascript:void(0)"
                                                                                               onclick="readNotifications('{{$notification->id}}')">
                                <div class="media">
                                    <div class="media-left align-self-center"><i
                                                class="fa {{ $notification->data['data']['icon'] }} icon-bg-circle {{ $notification->data['data']['background'] }}"></i>
                                    </div>
                                    <div class="media-body">
                                        <h6 class="media-heading"> {{ $notification->data['data']['title'] }}<span>-{{ $notification->data['data']['data'] }}</span></h6>
                                        {{-- <p class="notification-text font-small-3 text-muted"> {{ $notification->data['data']['data'] }}</p> --}}
                                        <p class="notification-text font-small-3 text-muted"><span>Subject: </span> {{ $notification->data['data']['background'] }}</p>
                                        <h6 class="media-heading text-danger"> <span>On date-{{ $notification->data['data']['icon'] }}</span></h6>
                                        <small>
                                            <time class="media-meta text-muted"
                                                  datetime="{{$notification->created_at}}"> {{ $notification->created_at->diffForHumans()}}
                                            </time>
                                        </small>
                                    </div>
                                </div>
                            </a></li>
                    @endforeach
                    @foreach(auth()->user()->readNotifications as $notification)
                        <li class="media-list  border-bottom-purple"><a href="javascript:void(0)"
                                                                        onclick="readNotifications('{{$notification->id}}')">
                                <div class="media">
                                    <div class="media-left align-self-center"><i
                                                class="fa {{ $notification->data['data']['icon'] }} icon-bg-circle {{ $notification->data['data']['background'] }}"></i>
                                    </div>
                                    <div class="media-body">
                                        <h6 class="media-heading"> {{ $notification->data['data']['title'] }} <span>-{{ $notification->data['data']['data'] }}</span></h6>
                                        @php
                                            $tkt = substr($notification->data['data']['data'], 4);
                                            $lead = \App\Models\lead\Lead::where('reference',$tkt)->first();
                                        @endphp
                                        {{-- <p class="notification-text font-small-3 text-muted"> {{ $notification->data['data']['data'] }}</p> --}}
                                        <p class="notification-text font-small-3 text-muted"><span>Subject: </span> {{ $notification->data['data']['background'] }}</p>
                                        <h6 class="media-heading text-danger"> <span>On date-{{ $notification->data['data']['icon'] }}</span></h6>
                                        <small>
                                            <time class="media-meta text-muted"
                                                  datetime="{{$notification->created_at}}"> {{ $notification->created_at->diffForHumans()}}
                                            </time>
                                        </small>
                                        <a href="{{ url('leads',@$lead->id) }}" class="btn btn-primary float-right" id="read-more" data-tkt="{{@$lead->id}}">Read More</a>
                                    </div>
                                </div>
                            </a></li>
                    @endforeach

                </section>
            </div>
        </div>
    </div>
@endsection
@section('extra-scripts')
<script>
    $('.section-click').on('click', '#read-more', function (e) { 
        var ticket = e.target.getAttribute('data-tkt')
    });
</script>
@endsection