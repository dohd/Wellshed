<style>
    :root{
      --bar:#4f46e5;        /* top bar indigo */
      --bubble-bot:#f1f5f9; /* light slate/gray bubble */
      --bubble-user:#e0e7ff;/* light indigo bubble */
      --ink:#0f172a;
    }
    html,body{height:100%}
    body{background:#ffffff;color:var(--ink)}
    .app {
      min-height:100dvh; display:flex; flex-direction:column;
    }
    /* Top Bar */
    .topbar{
      background:var(--bar); color:#fff;
      height:64px; display:flex; align-items:center;
    }
    .topbar .brand{
      font-weight:700; font-size:1.25rem;
    }
    .topbar .btn-outline-light{
      --bs-btn-color:#fff; --bs-btn-border-color:#ffffff55;
      --bs-btn-hover-bg:#ffffff22; --bs-btn-hover-color:#fff; --bs-btn-hover-border-color:#ffffff88;
      --bs-btn-active-bg:#ffffff33;
      border-width:2px; border-radius:999px; padding:.4rem 1rem;
    }
    /* Chat area */
    .chat-wrap{
      flex:1 1 auto; overflow:auto; padding:24px 0;
      background:#ffffff;
    }
    .chat-inner{ max-width:980px; margin:0 auto; padding:0 16px; }
    .msg{ max-width:72ch; border-radius:18px; padding:12px 16px; box-shadow:0 8px 20px rgba(2,6,23,.06); }
    .msg-bot{
      background:var(--bubble-bot);
      border:1px solid #e5e7eb;
    }
    .msg-user{
      background:var(--bubble-user);
      border:1px solid #c7d2fe;
    }
    .bubble-row{ margin-bottom:18px; }
    .bubble-row.user{ display:flex; justify-content:flex-end; }
    .bubble-row.bot{ display:flex; justify-content:flex-start; }
    .msg + .msg{ margin-top:10px; }
    /* Composer */
    .composer-wrap{
      border-top:1px solid #e5e7eb; background:#fff;
    }
    .composer{
      max-width:980px; margin:0 auto; padding:12px 16px 24px 16px;
    }
    .composer .form-control{
      border-radius:999px; padding-left:18px; padding-right:18px; height:52px;
      border:1px solid #e5e7eb;
    }
    .composer .btn-send{
      border-radius:999px; padding:0 22px; height:52px;
      background:var(--bar); color:#fff; border:none;
    }
    .composer .btn-send:hover{ filter:brightness(.95); }
    .placeholder-text{ color:#64748b; }
    @media (max-width:576px){
      .msg{ max-width:100%; }
    }
    

    /* Anchor tag wrapping  */
    .msg-bot a {
      overflow-wrap: anywhere;   /* modern browsers */
      word-break: break-word;    /* fallback for older browsers */
      display: inline-block;     /* optional: helps with alignment */
      max-width: 100%;           /* don’t exceed container */
    }
    .msg-user a {
      overflow-wrap: anywhere;   /* modern browsers */
      word-break: break-word;    /* fallback for older browsers */
      display: inline-block;     /* optional: helps with alignment */
      max-width: 100%;           /* don’t exceed container */
    }
  </style>