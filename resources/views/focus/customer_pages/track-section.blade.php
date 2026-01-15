<section class="w-100" id="track">
  <div class="glass-card pane h-100 w-100">
    <h5 class="mb-3">Track</h5>

    <div class="mini-map mb-3">
      <div class="pin"><i class="bi bi-geo-alt"></i></div>
    </div>

    <div class="glass-card p-3">
      <div class="small text-muted">Estimated Arrival:</div>
      <div class="fs-3 fw-bold" id="eta">30 min</div>
    </div>
    <div class="footer-nav mt-4 pt-3 border-top">
      <div class="d-flex justify-content-around">
        <a class="nav-pill active" href="{{ route('biller.customer_pages.home') }}"><i class="bi bi-house"></i><span>Home</span></a>
          <a class="nav-pill" href="{{ route('biller.customer_pages.orders') }}"><i class="bi bi-receipt"></i><span>Orders</span></a>
          <a class="nav-pill" href="{{ route('biller.customer_pages.track') }}"><i class="bi bi-geo-alt"></i><span>Track</span></a>
          <a class="nav-pill" href="{{ route('biller.customer_pages.profile') }}"><i class="bi bi-person"></i><span>Profile</span></a>
      </div>
    </div>
  </div>
</section>
