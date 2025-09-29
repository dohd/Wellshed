{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const apiKey = @json(@auth()->user()->business->omniconvo_key);
    const config = {
        ajax: { 
            headers: { 
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'X-Signature': apiKey,
            } 
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    $.ajaxSetup(config.ajax);
    $('.datepicker').datepicker(config.date);
    $('#clear').click(() => $('#start_date, #end_date, #source-filter').val(''));

    // Track session reload
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('isReloaded', 'true');
    });

    // Fetch list of Chats
    $('.chat-item-parent').html('');
    function queryChats() {
        $.post("{{ route('api.chatbot.query_chats') }}", {
            ins: "{{ auth()->user()->ins }}",
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            user_type: $('#source-filter').val(),
        })
        .then(data => {
            $('.chat-item-parent').html('');
            if (data && data.length) {
                data.forEach(v => {
                    const chatHtml = `
                        <div 
                            class="chat-item border rounded pl-1 pr-0 pb-0 mt-0"
                            data-id="${v.fb_id}" 
                            data-user-type="${v.user_type}"
                            data-last-message-id="${v.last_message_id}" 
                            data-country="${v.country}"
                            data-phone="${v.phone_no}"
                            data-last-timestamp="${v.last_timestamp}"
                        >
                            <div class="row no-gutters p-0 m-0">
                                <div class="col-2"><i class="fa fa-user-circle avatar" aria-hidden="true"></i></div>
                                <div class="col-7" style="padding-left:5px;">
                                    <div class="font-weight-bold text-muted" style="font-size: 18px;">${v.username}</div>
                                    <h5 style="margin-top: 3px; color:gray; font-weight:bold;">
                                        ${v.last_message.length > 20? v.last_message.substring(0, 20) + '...' : v.last_message}
                                    </h5>
                                </div>
                                <div class="col-3">
                                    <div class="text-muted small text-right" style="width: 80px;">[${v.last_date}]</div>
                                    <span class="msg-count badge badge-danger ml-2 ${!v.unread_count? 'd-none' : ''}">
                                        <span class="font-weight-bold" style="font-size: 15px">${v.unread_count}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        `;
                    $('.chat-item-parent').append(chatHtml);                    
                });

                const fbId = sessionStorage.getItem('fbId');
                const chatItem = $(`div[data-id=${fbId}]`);

                // Maintain active chat class
                $('.chat-item').removeClass('active');
                if (chatItem.length) {
                    chatItem.addClass('active');
                    // Refresh Chat on new messages
                    if (chatItem.find('.msg-count:not(.d-none)').length) {
                        chatItem.click();
                    }
                }

                // Refresh last-opened chat case of page reload
                if (sessionStorage.getItem('isReloaded')) {
                    sessionStorage.removeItem('isReloaded');
                    if (chatItem.length) chatItem.click();
                } 
            }
            // Poll again after 5 sec
            setTimeout(() => queryChats(), 5000);
        })
        .fail((xhr, status, error) => {
            // Poll again after 5 sec
            setTimeout(() => queryChats(), 5000);
        });
    };
    queryChats();

    // Click a Chat to fetch messages
    $(document).on('click', '.chat-item', function() {
        // Set Active Item
        $('.chat-item').removeClass('active');
        $(this).addClass('active');

        // Chat Meta-data
        const fbId = $(this).attr('data-id');
        const lastMsgId = $(this).attr('data-last-message-id');
        const userType = $(this).attr('data-user-type');
        const country = $(this).attr('data-country');
        const lastTimestamp = $(this).attr('data-last-timestamp');
        const phoneNo = $(this).attr('data-phone');
        $('#dtl-source').html(userType);
        $('#dtl-country').html(country);
        $('#dtl-timestamp').html(lastTimestamp);
        $('#dtl-phone').html(phoneNo);

        // Fetch the transcript via AJAX
        $("#chat-transcript").html('');
        $.post("{{ route('api.chatbot.transcripts') }}", {fb_id: fbId, ins: "{{ auth()->user()->ins }}"})
        .then((data) => {
            if (data && data.transcript) {
                data.transcript.forEach((message) => {
                    let messageHtml = '';
                    if (message.bot_id) {
                        messageHtml = `
                        <div message-id="${message.id}" class="d-flex flex-column align-items-start mb-2">
                            <div class="border rounded p-1 bg-white">
                                <div class="h5 text-primary">${message.message}</div>
                            </div>
                            <div>
                                <span class="text-secondary">${message.sender}</span>
                                <span class="text-muted small">[${message.timestamp}]</span>
                            </div>
                        </div>
                        `;
                    } else {
                        messageHtml = `
                            <div message-id="${message.id}" class="d-flex flex-column align-items-end mb-2">
                                <div class="border rounded p-1 bg-primary">
                                    <div class="h5 text-white">${message.message}</div>
                                </div>
                                <div>
                                    <span class="text-secondary">${message.sender}</span>
                                    <span class="text-muted small">[${message.timestamp}]</span>
                                </div>
                            </div>
                            `;
                    }
                    $("#chat-transcript").append(messageHtml);
                });

                // Set scroll to last read message
                const lastReadMsgId = data.transcript[0]['last_read_id'];
                const lastMsgId = data.transcript[data.transcript.length-1]['id'];
                if (lastReadMsgId) {
                    let heightMargin = 400;
                    if (lastReadMsgId == lastMsgId) heightMargin = 0;
                    $("#chat-transcript").animate({ scrollTop: $(`div[message-id="${lastReadMsgId}"]`).position().top - heightMargin }, 800);
                } else {
                    // Set Scroll to bottom by default
                    const contentCtnr = $("#chat-transcript")[0];
                    contentCtnr.scrollTop = contentCtnr.scrollHeight;
                }

                // Memorize the Open Chat
                sessionStorage.setItem('fbId', fbId);

                // Acknowledge message is read
                const ins = @json(auth()->user()->ins);
                confirmChatIsRead({ins, fb_id: fbId, last_message_id: lastMsgId});
            } else {
                $("#chat-transcript").html('<p class="text-danger">No transcript available for this chat.</p>');
            }
        }) 
        .fail(function () {
            $("#chat-transcript").html('<p class="text-danger">Failed to load chat transcript. Please try again later.</p>');
        });
    });

    // Acknowledge message is read
    function confirmChatIsRead($params) {
        $.post("{{ route('api.chatbot.read_chat') }}", $params)
        .then((data) => $(`div[data-id="${$params.fb_id}"]`).find('.msg-count').addClass('d-none'))
        .fail((xhr, status, error) => console.log(error));
    }

    // Disable/Enable submit button
    const sourceList = ['whatsapp', 'facebook', 'instagram', 'website'];
    $('#reply-text').on('keyup', function() {
        const message = $(this).val();
        const fbId = sessionStorage.getItem('fbId');
        const source = $('#dtl-source').html();
        if (message && fbId && sourceList.includes(source)) $('#submit-reply').removeAttr('disabled');
        else $('#submit-reply').attr('disabled', true);
    });
    
    // Submit Reply Click
    $('#submit-reply').click(function() {
        $(this).attr('disabled', true);
        $(this).html('<i class="fa fa-spinner spinner fa-lg"></i>');
        // ajax call
        addObject({
            url: "{{ route('biller.omniconvo.send_user_message') }}",
            form: {
                message: $('#reply-text').val(), 
                // fb_id: 'testUser',
                fb_id: sessionStorage.getItem('fbId'),
                user_type: $('#dtl-source').html(),
            },
        }, true);
    });
    // Success Ajax callback
    function trigger(res) {
        $('#submit-reply').removeAttr('disabled');
        $('#submit-reply').html('<i class="fa fa-paper-plane fa-lg"></i>');
        $('#reply-text').val('');
    }
    // Error Ajax callback
    function triggerError(res) {
        $('#submit-reply').removeAttr('disabled');
        $('#submit-reply').html('<i class="fa fa-paper-plane fa-lg"></i>');
    }

    // Display scroll-down arrow on scroll
    $('#chat-transcript').on('scroll', function() {
        let scrollTop = $(this).scrollTop();  // Distance scrolled from top
        let scrollHeight = $(this)[0].scrollHeight; // Total scrollable height
        let divHeight = $(this).innerHeight(); // Visible height of the div

        // if scroll is less than 90% of the height display arrow
        $('#scroll-down').css('display', 'none');
        if (scrollTop + divHeight <= scrollHeight * 0.90) { 
            $('#scroll-down').css('display', 'block');
        }
    });
    
    // Scroll the content down when button is clicked
    $('#scroll-down').click(function() {
        $('#chat-transcript')[0].scrollBy({
            top: 600, // Adjust value to control how far it scrolls each click
            behavior: 'smooth',
        });
    });

</script>