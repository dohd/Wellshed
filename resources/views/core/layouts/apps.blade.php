<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>@yield('title', 'Refill — Water Delivery')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{--brand:#1f6fd9;--soft:#f5f8ff;--ink:#0f172a;}
    body{background:var(--soft); color:#111827;}
    .app{max-width:1200px; margin-inline:auto; padding:24px 16px;}
    .glass-card{background:#fff; border:1px solid #e9eef7;
      border-radius:16px; box-shadow:0 4px 24px rgba(17,24,39,.04);}
    .pane{padding:22px;}
    .title{font-weight:700; color:var(--ink);}
    .cta-btn{background:var(--brand); border:0;}
    .cta-btn:hover{filter:brightness(.95);}
  </style>
  <!-- BEGIN: Vendor CSS-->
    {{ Html::style(mix('focus/app_end-'.visual().'.css')) }}
    {!! Html::style('core/app-assets/css-'.visual().'/core/menu/menu-types/horizontal-menu.css') !!}
    {!! Html::style('core/app-assets/vendors/css/forms/icheck/icheck.css') !!}
    {!! Html::style('core/app-assets/vendors/css/forms/icheck/custom.css') !!}
    @yield('after-styles')
    <!-- END: Vendor CSS-->
    <!-- BEGIN: Custom CSS-->
    {!! Html::style('core/assets/css/style-'.visual().'.css') !!}

  @stack('styles')
</head>

<body>
  <main class="app">
      @yield('content')
  </main>

  {{ Html::script(mix('js/app_end.js')) }}
        {{ Html::script('focus/js/control.js?b='.config('version.build')) }}
        {{ Html::script('focus/js/custom.js?b='.config('version.build')) }}
  @yield('after-scripts')
  @yield('extra-scripts')
  {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> --}}
</body>
</html>
