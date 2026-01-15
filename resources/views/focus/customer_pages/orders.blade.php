@extends('core.layouts.apps')
@section('title', 'Orders')

<style>
    .cart-drawer {
        position: fixed;
        bottom: 56px;
        left: 0;
        width: 100%;
        background: #fff;
        box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 16px 16px 0 0;
        padding: 1rem;
        transition: bottom .35s ease;
        z-index: 9998;
        max-height: 60vh;
        overflow-y: auto;
    }

    .cart-drawer.open {
        bottom: 56px;
    }

    .product-item.active {
        border: 2px solid #007bff;
        background: #e8f2ff;
    }

    .footer-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #ffffff;
        border-top: 1px solid #ddd;
        padding: .4rem 0;
        z-index: 9999;
    }

    .nav-pill {
        text-align: center;
        flex: 1;
        color: #666;
        font-size: 12px;
        text-decoration: none;
    }

    .nav-pill i {
        font-size: 20px;
        display: block;
    }

    .nav-pill.active {
        color: #007bff;
    }

    @media (max-width: 576px) {
        .nav-pill {
            font-size: 11px;
        }

        .cart-drawer {
            max-height: 65vh;
        }
    }
</style>

@section('content')

<section class="w-100">
    <div class="glass-card pane h-100 w-100 d-flex flex-column">

        <h5 class="mb-3">Select Product</h5>

        <!-- Search -->
        <input type="text" id="productSearch" class="form-control mb-3" placeholder="Search products...">

        <!-- Product List -->
        <div id="productList" class="flex-grow-1 overflow-auto" style="max-height: 55vh;">
            @foreach ($products as $item)
                <button class="list-tile w-100 text-start bg-white mb-2 product-item"
                    data-id="{{ $item['id'] }}"
                    data-name="{{ $item['name'] }}"
                    data-price="{{ $item['price'] }}"
                    data-fixedqty="{{ $item['qty'] ?? 1 }}"
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

        <!-- Cart Drawer -->
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

    </div>
</section>

<!-- Bottom Navigation -->
<div class="footer-nav">
    <div class="d-flex justify-content-around">
        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.home') ? 'active' : '' }}"
            href="{{ route('biller.customer_pages.home') }}">
            <i class="bi bi-house"></i>
            <span>Home</span>
        </a>

        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.orders') ? 'active' : '' }}"
            href="{{ route('biller.customer_pages.orders') }}">
            <i class="bi bi-receipt"></i>
            <span>Orders</span>
        </a>

        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.profile') ? 'active' : '' }}"
            href="{{ route('biller.customer_pages.profile') }}">
            <i class="bi bi-person"></i>
            <span>Profile</span>
        </a>

        <a class="nav-pill" href="{{ route('biller.logout') }}">
            <i class="bi bi-box-arrow-right"></i><span>Logout</span>
        </a>
    </div>
</div>

@endsection


@section('extra-scripts')
<script>
    let recurring = {{ $recurring ?? 1 }};
    console.log(recurring)

    $(function() {
        let selected = {};

        function renderSelection() {
            let total = 0;
            let listHtml = "";

            $.each(selected, function(id, item) {
                total += item.qty * item.price;

                listHtml += `
                    <li class="d-flex justify-content-between align-items-center mb-2">
                        ${item.name}
                        <div>
                            ${
                                recurring == 0
                                ? `
                                    <button class="btn btn-sm btn-light updateQty" data-id="${item.id}" data-delta="-1">−</button>
                                    <span class="mx-2">${item.qty}</span>
                                    <button class="btn btn-sm btn-light updateQty" data-id="${item.id}" data-delta="1">+</button>
                                  `
                                : `<span class="mx-2">${item.qty}</span>`
                            }
                        </div>
                    </li>
                `;
            });

            $("#selectedItems").html(listHtml);
            $("#totalCost").text("KSh " + total.toLocaleString());

            if (Object.keys(selected).length > 0) {
                $("#cartDrawer").addClass("open");
            } else {
                $("#cartDrawer").removeClass("open");
            }
        }

        /* Product Click */
        $(document).on("click", ".product-item", function() {

            let id = $(this).data("id");
            let name = $(this).data("name");
            let price = Number($(this).data("price"));
            let fixedQty = Number($(this).data("fixedqty")) || 1;

            if (recurring == 1) {
                selected = {};
                $(".product-item").removeClass("active");

                selected[id] = { id, name, price, qty: fixedQty };
                $(this).addClass("active");
            } else {
                if (!selected[id]) {
                    selected[id] = { id, name, price, qty: 1 };
                    $(this).addClass("active");
                } else {
                    delete selected[id];
                    $(this).removeClass("active");
                }
            }

            renderSelection();
        });

        /* Quantity Update (only recurring) */
        $(document).on("click", ".updateQty", function(e) {
            if (recurring == 1) return;

            e.stopPropagation();
            let id = $(this).data("id");
            let delta = Number($(this).data("delta"));

            selected[id].qty += delta;

            if (selected[id].qty <= 0) {
                delete selected[id];
                $('.product-item[data-id="' + id + '"]').removeClass("active");
            }

            renderSelection();
        });

        /* Product Search */
        $("#productSearch").on("input", function() {
            let term = $(this).val().toLowerCase();
            $(".product-item").each(function() {
                $(this).toggle($(this).data("name").toLowerCase().includes(term));
            });
        });

        $("#closeDrawer").click(function() {
            $("#cartDrawer").removeClass("open");
        });

        $("#btnContinue").click(function() {
            localStorage.setItem("cartItems", JSON.stringify(selected));
            window.location.href = "{{ route('biller.customer_pages.delivery') }}";
        });

        /* Auto-select first product when recurring=0 */
        if (recurring == 1) {
            $(".product-item").first().click();
        }
    });
</script>
@endsection
