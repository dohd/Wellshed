<?php $class = $thread->isUnread(Auth::id()) ? 'avatar-away' : '';
$class_alert = $thread->isUnread(Auth::id()) ? 'alert alert-light ' : '';

?>

<div class="m-1 pb-1 border-bottom-blue-grey border-bottom-lighten-5 {{$class_alert}}">
    <a href="{{ route('biller.messages.show', $thread->id) }}" class="media border-0">
        <div class="media-left pr-1">
                                    <span class="avatar avatar-md {{$class}}"><img class="media-object rounded-circle"
                                                                                   src="{{ Storage::disk('public')->url('app/public/img/users/' . @$thread->creator()->picture) }}"
                                                                                   alt="{{ $thread->creator()->first_name }}">
                                        <i></i>
                                    </span>
        </div>
        <div class="media-body w-100">
            <h6 class="list-group-item-heading">{{ $thread->subject }}<span
                        class="font-small-3 float-right primary">{{@$thread->latestMessage->updated_at}}</span></h6>
            <p class="list-group-item-text text-muted mb-0">@if(!$class)<i
                        class="ft-check primary font-small-2"></i>@endif {{ @$thread->latestMessage->body }} <span
                        class="float-right primary"><span class="badge badge-pill badge-danger">     ({{ $thread->userUnreadMessagesCount(Auth::id()) }})</span></span>
            </p><small>{{ $thread->creator()->first_name }}  {{ $thread->creator()->last_name }}</small>
            <small><strong> </strong> {{ $thread->participantsString(Auth::id(),array('first_name','last_name')) }}
            </small>
        </div>
    </a></div>