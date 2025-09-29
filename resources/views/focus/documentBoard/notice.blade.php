<!DOCTYPE html>


    <div class="content-wrapper">

        @permission('create-company-notice')
            <div class="content-header row mb-1">
                <div class="content-header-left col-6">
                    <h2 class=" mb-0"> Company Notice </h2>
                </div>
                <div class="content-header-right col-6">
                    <div class="media width-250 float-right">
                        <div class="media-body media-right text-right">
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <a href="{{ route( 'biller.company-notice-board.create-notice' ) }}" class="btn btn-facebook  btn-lighten-3 round">
                                    <i class="fa fa-plus-circle"></i> Draft Company Notice
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endauth


        <div class="content-body">
            <div class="row">
                <div class="col-12">

                    <div class="col-12">
                        <textarea id="message" name="message" class="tiny-display" rows="4" placeholder="Welcome Message isn't available... yet.">
                            @if($noticeMessage)
                                {{ $noticeMessage->message }}
                            @else
                                {{$noticeTemplate}}
                            @endif
                        </textarea>
                    </div>

                </div>
            </div>
        </div>
    </div>

