@extends ('core.layouts.app')
@section ('title', 'AI Analytics')

@include('focus.ai.styles.css')

@section('content')
<div class="container mb-2">
  <div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
      <main class="app">
        <!-- Top Bar -->
        <div class="topbar bg-transparent border-0 shadow-none">
          <div class="container position-relative">
            <div class="d-flex align-items-center justify-content-between">
              <div class="brand invisible">&nbsp;</div> <!-- hide brand, keep layout -->
              <button class="btn btn-primary btn-sm" onclick="startNewChart()">Start New Chat</button>
            </div>
          </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-wrap" id="chatScroll">
          <div class="chat-inner">
            <!-- Bot greeting -->
            <div class="bubble-row bot">
              <div class="msg msg-bot">
                Hello! I'm your analytics assistant, ready to answer your questions about the business metrics.
              </div>
            </div>
          </div>
        </div>

        <!-- Composer -->
        <div class="composer-wrap">
          <div class="composer">
            <form id="composerForm" class="form-row align-items-center" autocomplete="off">
              <div class="col-12 col-sm">
                <label for="userInput" class="sr-only">Ask</label>
                <textarea id="userInput" rows="1" class="form-control mt-1" placeholder="Ask about the business metrics..."></textarea>
              </div>
              <div class="col-12 col-sm-auto">
                <button class="btn btn-send btn-block mt-1" type="submit" id="submitBtn">
                  <i class="fa fa-paper-plane" aria-hidden="true"></i> Send
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>        
    </div>
  </div>
</div>
@endsection

@section('after-scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
  const chat = document.querySelector('.chat-inner');
  const scrollWrap = document.getElementById('chatScroll');
  const form = document.getElementById('composerForm');
  const input = document.getElementById('userInput');

  function startNewChart() {
    chat.innerHTML = '';
  }

  function addBubble(text, who){
    const row = document.createElement('div');
    const bubble = document.createElement('div');
    row.className = 'bubble-row ' + (who === 'user' ? 'user' : 'bot');
    bubble.className = 'msg ' + (who === 'user' ? 'msg-user' : 'msg-bot');

    // Regex to match URLs or anchor tags
    const linkRegex = /(<a\b[^>]*>.*?<\/a>|https?:\/\/[^\s]+)/gi;
    const parts = text.split(linkRegex);

    // Helper: safe-append HTML string by parsing into a fragment
    const appendHTML = (html, target) => {
      const tpl = document.createElement('template');
      tpl.innerHTML = html;
      target.appendChild(tpl.content);
    };

    // Helper: reliable test for linkRegex (resets lastIndex if global)
    const isLink = (s) => {
      if (!s) return false;
      if (linkRegex.global) linkRegex.lastIndex = 0;
      return linkRegex.test(s);
    };

    const frag = document.createDocumentFragment();

    parts.forEach((part) => {
      if (!part) return;

      if (isLink(part)) {
        // Link or <a ...> already?
        const trimmed = part.trim();

        if (trimmed.toLowerCase().startsWith('<a')) {
          // Already an anchor tag — parse & append
          appendHTML(trimmed, frag);
        } else {
          // Raw URL — create <a>
          const a = document.createElement('a');
          a.href = part;
          a.textContent = part;
          a.target = '_blank';
          a.rel = 'noopener noreferrer';
          frag.appendChild(a);
        }
      } else {
        // Plain text (optionally render markdown)
        if (typeof marked !== 'undefined' && marked?.parse) {
          const html = marked.parse(part);
          appendHTML(html, frag);
        } else {
          frag.appendChild(document.createTextNode(part));
        }
      }
    });

    // One DOM write
    bubble.appendChild(frag);
    row.appendChild(bubble);
    chat.appendChild(row);

    // auto-scroll
    requestAnimationFrame(() => { 
      scrollWrap.scrollTop = scrollWrap.scrollHeight; 
    });
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if(!text) return;
    addBubble(text, 'user');
    input.value = '';

    const data = [];
    {{-- const query = {
      "model": "gpt-5",
      "input": [
          {
            "role": "system",
            "content": "Answer ONLY from the provided summary. If missing, say you don't know."
          },
          {
            "role": "user",
            "content": `Summary:\n\n ${data}`,
          },
          {
            "role": "user",
            "content": `Question: ${text}`
          }
      ]
    };
    queryGPT(query)
    .then(data => {
      if (data?.output.length) {
        contentText = data.output[1]['content'][0]['text'] || '';
        if (contentText) {
          addBubble(contentText, 'bot');
        }
      }
    })
    .catch(error => console.error('Error:', error)); --}}

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<div class="spinner-border text-white" role="status">
      <span class="visually-hidden"></span>
    </div>`;

    // prompt N8N
    const url = @json(config('n8n.prompt_url'));
    const scope_id = @json($business->id);
    const host = @json(request()->getHost());
    fetch(`${url}`, {
      method: 'POST', // HTTP method
      headers: {
        'Content-Type': 'application/json', // tell server we're sending JSON
        'Alternate-Host': host,
      },
      body: JSON.stringify({text, scope_id}),
    })
    .then(response => {
      if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
      return response.json(); // parse JSON response
    })
    .then(data => {
      if (data?.message) {
        const contentText = data.message.content || '';
        if (contentText) {
          addBubble(contentText, 'bot');
        }
      }
      submitBtn.disabled = false;
      submitBtn.innerHTML = `<i class="fa fa-paper-plane" aria-hidden="true"></i> Send`;
    })
    .catch((xhr, status, error) => {
      addBubble('Oops! Could not give feedback, something went wrong', 'bot');
      submitBtn.disabled = false;
      submitBtn.innerHTML = `<i class="fa fa-paper-plane" aria-hidden="true"></i> Send`;
    });
  });

  function queryGPT(data) {
    const url = @json(config('openai.api_url'));
    const token = @json(config('openai.api_token'));
    return fetch(`${url}/responses`, {
      method: 'POST', // HTTP method
      headers: {
        'Content-Type': 'application/json', // tell server we're sending JSON
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(data),
    })
    .then(response => {
      if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
      return response.json(); // parse JSON response
    })
  }
</script>
@endsection
