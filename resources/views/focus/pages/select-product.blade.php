<section class="w-100">
  <div class="glass-card pane h-100 w-100 d-flex flex-column">

    <h5 class="mb-3">Select Product</h5>

    <!-- ✅ Search -->
    <input type="text" id="productSearch" class="form-control mb-3"
      placeholder="Search products...">

    <!-- ✅ Scrollable, multi-select list -->
    <div id="productList" class="flex-grow-1 overflow-auto" style="max-height: 55vh;">
      @foreach($products as $item)
        <button class="list-tile w-100 text-start bg-white mb-2 product-item"
          data-id="{{ $item['id'] }}"
          data-name="{{ $item['name'] }}"
          data-price="{{ $item['price'] }}"
          style="padding: 1rem;">
          
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold">{{ $item['name'] }}</div>
              <div class="text-muted small">{{ $item['eta'] }}</div>
            </div>
            <div class="price fw-bold">KSh {{ number_format($item['price']) }}</div>
          </div>
        </button>
      @endforeach
    </div>
    <!-- Slide-Up Cart Drawer -->
    <div id="cartDrawer" class="cart-drawer">
        <div class="drawer-header d-flex justify-content-between align-items-center">
            <strong>Your Cart</strong>
            <button id="closeDrawer" class="btn-close btn-sm"></button>
        </div>

        <ul id="selectedItems" class="list-unstyled mb-3"></ul>

        <div class="d-flex justify-content-between fw-bold mb-3">
            <span>Total</span>
            <span id="totalCost">KSh 0</span>
        </div>

        <button class="btn btn-primary w-100" id="btnContinue">
            Continue
        </button>
    </div>


    <!-- ✅ Summary -->
    <div class="alert alert-light border mt-3 mb-0">
      <div class="fw-semibold">Selected Items</div>
      <ul id="selectedItems" class="list-unstyled small mb-2"></ul>
      <div class="d-flex justify-content-between fw-bold">
        <span>Total:</span>
        <span id="totalCost">KSh 0</span>
      </div>
    </div>

    <!-- ✅ Navigation -->
    <div class="footer-nav mt-4 pt-3 border-top">
      <div class="d-flex justify-content-around">
        <button class="btn btn-primary w-100" id="btnContinue">
          Continue
        </button>
      </div>
    </div>
  </div>
</section>
