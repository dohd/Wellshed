@extends ('core.layouts.app')
@section ('title', 'WhatsApp Setup')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-6">
            <h4 class="content-header-title mb-0">WhatsApp Setup</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
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
                              <h2 class="mb-2">🛠️ Prerequisites for WhatsApp Embedded Signup</h2>
                              <ul class="list-group">
                                <li class="list-group-item">
                                  ✅ A <strong>Facebook Business Manager</strong> account
                                </li>
                                <li class="list-group-item">
                                  ✅ A <strong>Facebook App</strong> with the WhatsApp product added
                                </li>
                                <li class="list-group-item">
                                  ✅ A <strong>Dedicated phone number</strong> (not tied to a personal WhatsApp account)
                                </li>
                                <li class="list-group-item">
                                  ✅ <strong>Admin access</strong> to your BSP dashboard or integration platform
                                </li>
                              </ul>
                            </div>

                            <div id="fb-root"></div>
                              <button onclick="launchWhatsAppSignup()" class="btn btn-info">
                                  WhatsApp Signup
                              </button>
                              <br><br>
                              <h5>Session info response:</h5>
                              <pre id="session-info-response"></pre>
                              </br>
                              <h5>SDK response:</h5>
                              <pre id="sdk-response"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<!-- Start Meta Scripts -->
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }
    });

    window.addEventListener('message', (event) => {
      if (event.origin !== "https://www.facebook.com" && event.origin !== "https://web.facebook.com") {
        return;
      }
      try {
        const data = JSON.parse(event.data);
        if (data.type === 'WA_EMBEDDED_SIGNUP') {
          // if user finishes the Embedded Signup flow
          if (data.event === 'FINISH') {
            const {phone_number_id, waba_id} = data.data;
            console.log("Phone number ID ", phone_number_id, " WhatsApp business account ID ", waba_id);
            // if user cancels the Embedded Signup flow
          } else if (data.event === 'CANCEL') {
            const {current_step} = data.data;
            console.warn("Cancel at ", current_step);
            // if user reports an error during the Embedded Signup flow
          } else if (data.event === 'ERROR') {
            const {error_message} = data.data;
            console.error("error ", error_message);
          }
        }
        document.getElementById("session-info-response").textContent = JSON.stringify(data, null, 2);
      } catch {
        console.log('Non JSON Responses', event.data);
      }
    });

    const fbLoginCallback = (response) => {
    if (response.authResponse) {
        const code = response.authResponse.code;
        // The returned code must be transmitted to your backend first and then
        // perform a server-to-server call from there to our servers for an access token.
      }
      document.getElementById("sdk-response").textContent = JSON.stringify(response, null, 2);
    }
    const launchWhatsAppSignup = () => {
      const configId = @json($business->whatsapp_business_config_id);
      // Launch Facebook login
      FB.login(fbLoginCallback, {
        config_id: configId, // configuration ID goes here
        response_type: 'code', // must be set to 'code' for System User access token
        override_default_response_type: true, // when true, any response types passed in the "response_type" will take precedence over the default types
        extras: {"version":"v3"}
      });
    }

    window.fbAsyncInit = function() {
      const developerAppId = @json($business->meta_developer_app_id);
      FB.init({
        appId            : developerAppId,
        autoLogAppEvents : true,
        xfbml            : true,
        version          : 'v23.0'
      });
    };
</script>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
<!-- End Meta Scripts -->
@endsection
