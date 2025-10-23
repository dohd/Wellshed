<!-- BEGIN: Header-->
<nav
        class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-static-top navbar-dark bg-gradient-x-grey-blue navbar-border navbar-brand-center">
    <div class="navbar-wrapper">
        <div class="navbar-header">
            <ul class="nav navbar-nav flex-row">
                <li class="nav-item mobile-menu d-md-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs"
                                                                      href="#"><i class="ft-menu font-large-1"></i></a></li>
                <li class="nav-item"><a class="navbar-brand" href="{{ route('biller.dashboard') }}"><img
                                class="brand-logo" alt="Brand Logo"
                                src="{{ Storage::disk('public')->url('app/public/img/company/theme/' . config('core.theme_logo')) }}">
                    </a></li>
                <li class="nav-item d-md-none"><a class="nav-link open-navbar-container" data-toggle="collapse"
                                                  data-target="#navbar-mobile"><i class="fa fa-ellipsis-v"></i></a></li>
            </ul>
        </div>
        <div class="navbar-container content">
            <div class="collapse navbar-collapse" id="navbar-mobile">
                <ul class="nav navbar-nav mr-auto float-left">
                    <li class="nav-item d-none d-md-block">
                        <a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu"></i></a>
                    </li>
                    @permission('business_settings')
                        <li class="dropdown nav-item mega-dropdown">
                            <a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown">{{ trans('business.business_admin') }}</a>
                            <ul class="mega-dropdown-menu dropdown-menu row">
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase mb-1">
                                        <i class="fa fa-building-o"></i>{{ trans('business.general_preference') }}
                                    </h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{ route('biller.business.settings') }}"><i class="ft-feather"></i>{{ trans('business.company_settings') }}</a></li>
                                                <li><a class="dropdown-item" href="{{ route('biller.settings.localization') }}"><i class="fa fa-globe"></i>{{ trans('business.business_localization') }} </a></li>
                                                <li><a class="dropdown-item" href="{{ route('biller.settings.currency_exchange') }}"><i class="fa fa-retweet"></i> {{ trans('currencies.currency_exchange') }}</a></li>
                                                <li><a class="dropdown-item" href="{{ route('biller.settings.opening_stock') }}"><i class="fa fa-cubes"></i> Opening Stock</a></li>
                                                <li><a class="dropdown-item" href="{{ route('biller.settings.status') }}"><i class="fa fa-flag-o"></i> {{ trans('meta.default_status') }}</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-random"></i>
                                        {{ trans('business.billing_settings') }}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.settings.billing_preference') }}"><i
                                                                class="fa fa-files-o"></i>
                                                        {{ trans('business.billing_settings_preference') }}
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.additionals.index') }}"><i
                                                                class="fa fa-floppy-o"></i>
                                                        {{ trans('business.tax_discount_management') }}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{ route('biller.prefixes.index') }}"><i
                                                                class="fa fa-bookmark-o"></i>
                                                        {{ trans('business.prefix_management') }}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{ route('biller.terms.index') }}"><i
                                                                class="fa fa-gavel"></i>
                                                        {{ trans('business.terms_management') }}
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.settings.pos_preference') }}"><i
                                                                class="fa fa-shopping-cart"></i> {{ trans('pos.preference') }}
                                                    </a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-money"></i>
                                        {{ trans('business.payment_account_settings') }}
                                    </h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.settings.payment_preference') }}"><i
                                                                class="fa fa-credit-card"></i>
                                                        {{ trans('business.payment_preferences') }}
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.currencies.index') }}"><i
                                                                class="fa fa-money"></i>
                                                        {{ trans('business.currency_management') }}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{ route('biller.banks.index') }}"><i
                                                                class="ft-server"></i> {{ trans('business.bank_accounts') }}
                                                    </a>
                                                </li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.usergatewayentries.index') }}"><i
                                                                class="fa fa-server"></i>
                                                        {{ trans('usergatewayentries.usergatewayentries') }}
                                                    </a>
                                                </li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.settings.accounts') }}"><i
                                                                class="ft-compass"></i>
                                                        {{ trans('business.accounts_settings') }}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('biller.settings.reclassify_transactions') }}">
                                                        <i class="ft-minimize-2"></i> Reclassify Transactions
                                                    </a>
                                                </li>
                                                @permission('reclassify-purchases')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('biller.purchase-class-reclassify') }}">
                                                            <i class="ft-flag"></i> Reclassify Purchases
                                                        </a>
                                                    </li>
                                                @endauth
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="ft-at-sign"></i>
                                        {{ trans('business.communication_settings') }}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.business.email_sms_settings') }}"><i
                                                                class="ft-minimize-2"></i>
                                                        {{ trans('meta.email_sms_settings') }}
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.send_sms.notification_email_sms') }}"><i
                                                                class="ft-activity"></i> Sms and Email Notification Recipient
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.mpesa_configs.index') }}"><i
                                                                class="ft-activity"></i> Mpesa Configs
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.settings.notification_email') }}"><i
                                                                class="ft-activity"></i> {{ trans('meta.notification_email') }}
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.templates.index') }}"><i
                                                                class="fa fa-comments"></i> {{ trans('templates.manage') }}
                                                    </a></li>
                                                
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('biller.company-notice-board.central') }}">
                                                        <i class="fa fa-bullhorn"></i> Communications Board
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ \Illuminate\Support\Facades\Auth::user()->ins === 2 ? route('biller.marquee.index') : route('biller.marquee.create') }}">
                                                        <i class="fa fa-flag-o"></i> Marquee Message
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-random"></i>
                                        {{ trans('business.miscellaneous_settings') }}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{ route('biller.customfields.index') }}"><i class="ft-anchor"></i>{{ trans('customfields.customfields') }}</a></li>
                                                <li><a class="dropdown-item" href="{{ route('biller.classlists.index') }}"><i class="fa fa-columns"></i> Manage Branch/Class List/Dept</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>

                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-cogs"></i>
                                        {{ trans('business.advanced_settings') }}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{ route('biller.cron') }}"><i
                                                                class="fa fa-terminal"></i> {{ trans('meta.cron') }}
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.web_update_wizard') }}"><i
                                                                class="fa fa-magic"></i> {{ trans('update.web_updater') }}
                                                    </a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-asterisk"></i>
                                        {{ trans('business.crm_hrm_settings') }}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.settings.crm_hrm_section') }}"><i
                                                                class="fa fa-indent"></i> {{ trans('meta.self_attendance') }}
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.settings.crm_hrm_section') }}"><i
                                                                class="fa fa-key"></i> {{ trans('meta.customer_login') }}
                                                    </a>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-camera-retro"></i>
                                        {{ trans('business.visual_settings') }}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item"
                                                       href="{{ route('biller.settings.theme') }}"><i
                                                                class="fa fa-columns"></i>
                                                        {{ trans('meta.employee_panel_theme') }}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{ route('biller.about') }}"><i
                                                                class="fa fa-info-circle"></i>
                                                        {{ trans('update.about_system') }}
                                                    </a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    @endauth

                    
                    <li class="nav-item d-none d-md-block"><a class="nav-link nav-link-expand" href="#"><i
                                    class="ficon ft-maximize"></i></a></li>
                    <li class="dropdown">
                        <a href="#" class="nav-link " data-toggle="dropdown" role="button"
                           aria-expanded="false">
                            <i class="ficon ft-toggle-left"></i> </a>
                        <ul class="dropdown-menu lang-menu" role="menu">
                            <li class="dropdown-item"><a href="{{ route('direction', ['ltr']) }}"><i
                                            class="ficon ft-layout"></i> {{ trans('meta.ltr') }}</a></li>
                            <li class="dropdown-item"><a href="{{ route('direction', ['rtl']) }}"><i
                                            class="ficon ft-layout"></i> {{ trans('meta.rtl') }}</a></li>
                        </ul>
                    </li>

                    <li class="nav-item ">
                        <a href="{{ route('biller.dashboard') }}" class="btn round mt_6"> {{ @$logged_in_user->business->cname }} </a>
                    </li>
                </ul>

                <div class="nav navbar-nav float-right mr-2" style="color: white;">V2.01 2024</div>
                <ul class="nav navbar-nav float-right">
                    @if (config('locale.status') && count(config('locale.languages')) > 1)
                        <li class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="false">
                                {{ trans('menus.language-picker.language') }}
                                <span class="caret"></span>
                            </a>
                            @include('includes.partials.lang_focus')
                        </li>
                    @endif
                    <li class="dropdown dropdown-notification nav-item"><a class="nav-link nav-link-label"
                                                                           href="#" data-toggle="dropdown" onclick="loadNotifications()"><i
                                    class="ficon ft-bell"></i><span class="badge badge-pill badge-danger badge-up"
                                                                    id="n_count">{{ auth()->user()->unreadNotifications->count() }}</span></a>
                        <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right" id="user_notifications">
                        </ul>
                    </li>
                    <li class="dropdown dropdown-notification nav-item"><a class="nav-link nav-link-label"
                                                                           href="#" data-toggle="dropdown">
                            @if (session('clock', false))
                                <i class="ficon ft-clock spinner"></i>
                                <span class="badge badge-pill badge-info badge-up">{{ trans('general.on') }}</span>
                            @else
                                <i class="ficon ft-clock"></i>
                                <span class="badge badge-pill badge-danger badge-up">
                                    {{ trans('general.off') }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                            <li class="scrollable-container media-list">
                                <div class="media">
                                    <div class="media-body text-center">
                                        @if (!session('clock', false))
                                            <a href="{{ route('biller.clock') }}" class="btn btn-success"><i
                                                        class="ficon ft-clock spinner"></i>
                                                {{ trans('hrms.clock_in') }}</a>
                                        @else
                                            <a href="{{ route('biller.clock') }}" class="btn btn-secondary"><i
                                                        class="ficon ft-clock"></i> {{ trans('hrms.clock_out') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown dropdown-notification nav-item"><a class="nav-link nav-link-label"
                                                                           href="{{ route('biller.messages') }}"><i class="ficon ft-mail"></i><span
                                    class="badge badge-pill badge-warning badge-up">{{ Auth::user()->newThreadsCount() }}</span></a>
                    </li>
                    <li class="dropdown dropdown-user nav-item"><a class="dropdown-toggle nav-link dropdown-user-link"
                                                                   href="#" data-toggle="dropdown"><span class="avatar avatar-online"><img
                                        src="{{ Storage::disk('public')->url('app/public/img/users/' . @$logged_in_user->picture) }}"
                                        alt=""><i></i></span><span
                                    class="user-name">{{ $logged_in_user->name }}</span></a>
                        <div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item"
                                                                          href="{{ route('biller.profile') }}"><i class="ft-user"></i>
                                {{ trans('navs.frontend.user.account') }}</a><a class="dropdown-item"
                                                                                href="{{ route('biller.messages') }}"><i class="ft-mail"></i> My
                                Inbox</a><a class="dropdown-item" href="{{ route('biller.todo') }}"><i
                                        class="ft-check-square"></i>
                                {{ trans('general.tasks') }}</a><a class="dropdown-item"
                                                                   href="{{ route('biller.attendance') }}"><i class="ft-activity"></i>
                                {{ trans('hrms.attendance') }}</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('biller.logout') }}"><i class="ft-power"></i>
                                {{ trans('navs.general.logout') }}</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<!-- END: Header-->

<!-- BEGIN: Main Menu-->
<div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-light navbar-without-dd-arrow navbar-shadow menu-border"
     role="navigation" data-menu="menu-wrapper">
    <!-- Horizontal menu content-->
    <div class="navbar-container main-menu-content" data-menu="menu-container">
        <ul class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="dropdown nav-item">
                <a href="{{ route('biller.dashboard') }}" class="nav-link {{ strpos(Route::currentRouteName(), 'biller.dashboard') === 0 ? 'active' : '' }}">
                    <i class="ft-home"></i><span>{{ trans('navs.frontend.dashboard') }}</span>
                </a>                            
            </li>

            <!-- CRM module -->
            <li class="dropdown nav-item" data-menu="dropdown">
                <a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown">
                    <i class="icon-diamond"></i><span>{{ trans('features.crm') }}</span>
                </a>                                
                <ul class="dropdown-menu">
                    <!-- Subscription Packages -->
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                            <i class="fa fa-ship" aria-hidden="true"></i>Subscription Packages
                        </a>                                        
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.subpackages.index') }}"
                                   data-toggle="dropdown"><i class="ft-list"></i> Manage Packages
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('biller.subpackages.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Package
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Target Zones -->
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                            <i class="fa fa-truck"></i></i> Target Zones
                        </a>                                        
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.target_zones.index') }}"
                                   data-toggle="dropdown"><i class="ft-list"></i> Manage Target Zones
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('biller.target_zones.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Target Zones
                                </a>
                            </li>
                        </ul>
                    </li>  
                    <!-- customer -->
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                            <i class="ft-users"></i></i> Customer Management
                        </a>                                    
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('biller.customers.index') }}" data-toggle="dropdown">
                                   <i class="ft-list"></i> Manage Customers
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('biller.customers.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i>
                                    {{ trans('labels.backend.customers.create') }}
                                </a>
                            </li>
                         </li>
                        </ul>
                    </li>  

                    <!-- Subscriptions -->
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                            <i class="fa fa-handshake-o" aria-hidden="true"></i>Subscriptions
                        </a>                                        
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.subscriptions.index') }}"
                                   data-toggle="dropdown"><i class="ft-list"></i> Manage Subscriptions
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('biller.subscriptions.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Subscription
                                </a>
                            </li>
                        </ul>
                    </li>                                             
                  
                    <!-- Orders -->
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-shopping-cart"></i></i> Orders</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.customer_orders.index') }}"
                                   data-toggle="dropdown"><i class="ft-list"></i> Manage Orders
                                </a>
                            </li>
                            @permission('create-branch')
                            <li><a class="dropdown-item" href="{{ route('biller.customer_orders.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Order
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>

                    <!-- Delivery Schedule -->
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-calendar"></i></i> Delivery Schedules</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.delivery_schedules.index') }}"
                                   data-toggle="dropdown"><i class="ft-list"></i> Manage Delivery Schedule
                                </a>
                            </li>
                            @permission('create-branch*')
                            <li><a class="dropdown-item" href="{{ route('biller.delivery_schedules.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Delivery Schedule
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>

                    <!-- Delivery -->
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-truck"></i></i> Order Deliveries</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.deliveries.index') }}"
                                   data-toggle="dropdown"><i class="ft-list"></i> Manage Delivery
                                </a>
                            </li>
                            @permission('create-branch')
                            <li><a class="dropdown-item" href="{{ route('biller.deliveries.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Delivery
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>

                    <!-- AI Agent Leads -->
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.whatsapp.setup') }}" data-toggle="dropdown"><i class="fa fa-cogs" aria-hidden="true"></i> WhatsApp Setup </a></li>
                            <li><a class="dropdown-item" href="{{ route('biller.whatsapp.templates.index') }}" data-toggle="dropdown"><i class="icon-badge" aria-hidden="true"></i> Message Templates</a></li>
                            <li><a class="dropdown-item" href="{{ route('biller.whatsapp.messages.index') }}" data-toggle="dropdown"><i class="fa fa-bullhorn" aria-hidden="true"></i> WhatsApp Broadcast / Single Message</a></li>
                        </ul>
                    </li>
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-android" aria-hidden="true"></i>AI Assistant</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.ai.analytics') }}" data-toggle="dropdown"><i class="fa fa-comments-o" aria-hidden="true"></i> AI Analytics</a></li>
                        </ul>
                    </li>
                </ul>
            </li>

            {{-- inventory module --}}
            @if(access()->allow('stock'))
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="ft-layers"></i><span>Inventory</span></a>
                    <ul class="dropdown-menu">
                         {{-- Stock Transfer --}}
                         @permission('manage-stock-transfer')
                         <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                             <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-wind"></i> {{ trans('products.stock_transfer') }}
                             </a>
                             <ul class="dropdown-menu">
                                 @permission('create-stock-transfer')
                                 <li><a class="dropdown-item" href="{{ route('biller.stock_transfers.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Transfer
                                     </a>
                                 </li>
                                 @endauth
                                 <li><a class="dropdown-item" href="{{ route('biller.stock_transfers.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Stock Transfers</a></li>
                                 <li><a class="dropdown-item" href="{{ route('biller.stock_rcvs.index') }}" data-toggle="dropdown">  <i class="ft-list"></i> Stock Receiving</a></li>
                             </ul>
                         </li>
                         @endauth
     
                         {{-- Stock Adjustment --}}
                         @permission('manage-stock-adj')
                         <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                             <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-balance-scale"></i> Stock Adjustment</a>
                             <ul class="dropdown-menu">
                                 <li>
                                     <a class="dropdown-item" href="{{ route('biller.stock_adjs.index') }}" data-toggle="dropdown"><i class="ft-file-text"></i> Manage Adjustments</a>
                                 </li>
                                  @permission('create-stock-adj')
                                 <li>
                                     <a class="dropdown-item" href="{{ route('biller.stock_adjs.create')}}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Adjustment</a>
                                 </li>
                                  @endauth
                             </ul>
                         </li>
                          @endauth

                          {{-- Product Management --}}
                        @permission('manage-product')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-cube"></i> {{ trans('labels.backend.products.management') }}</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.products.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> {{ trans('labels.backend.products.management') }}
                                    </a>
                                </li>
                                @permission('create-product')
                                <li><a class="dropdown-item" href="{{ route('biller.products.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> {{ trans('labels.backend.products.create') }}
                                    </a>
                                </li>
                                @endauth        
                            </ul>
                        </li>
                        @endauth

                        {{-- Product Category --}}
                        @permission('manage-product-category')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-object-ungroup"></i> Product Categories
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.productcategories.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Categories Management
                                    </a>
                                </li>
                                @permission('create-product-category')
                                <li><a class="dropdown-item" href="{{ route('biller.productcategories.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> {{ trans('labels.backend.productcategories.create') }}
                                    </a>
                                </li>
                                @endauth                                
                            </ul>
                        </li>
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-package"></i> Product UoM</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.productvariables.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Product UoM
                                    </a>
                                </li>
                                @permission('create-product')
                                <li><a class="dropdown-item" href="{{ route('biller.productvariables.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Product UoM
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Warehouse --}}
                        @permission('manage-warehouse')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-building-o"></i> Warehouses
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.warehouses.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Warehouse Management
                                    </a>
                                </li>
                                @permission('create-warehouse')
                                <li><a class="dropdown-item" href="{{ route('biller.warehouses.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Warehouse
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                    </ul>
                </li>
            @endif        
        
            {{-- human resource module --}}
            @if (access()->allow('hrm'))
                <li class="dropdown nav-item" data-menu="dropdown">
                    <a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown">
                        <i class="icon-badge"></i><span>User Access</span>
                    </a>                            
                    <ul class="dropdown-menu">
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                                <i class="fa fa-users"></i> User Management 
                            </a>                                        
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.hrms.index') }}" data-toggle="dropdown">
                                        <i class="ft-list"></i>Manage Users                                      
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.hrms.create') }}" data-toggle="dropdown"> 
                                       <i class="fa fa-plus-circle"></i>Create User                                        
                                    </a>
                                </li>                                     
                            </ul>
                        </li>      
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                                <i class="icon-badge"></i> Roles Management 
                            </a>                                        
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.role.index') }}" data-toggle="dropdown"> 
                                       <i class="ft-list"></i> Manage Roles
                                   </a>                                        
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.role.create') }}" data-toggle="dropdown"> 
                                       <i class="fa fa-plus-circle"></i>Create Role                                        
                                    </a>
                                </li> 
                            </ul>
                        </li>                 
                    </ul>
                </li>
            @endif

            @permission('manage-daily-logs')
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="icon-clock"></i><span>Daily Logs</span></a>
                <ul class="dropdown-menu"> 
                    {{-- sending sms --}}
                    @permission('manage-sms_send')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-mail"></i> Message Template</a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('biller.message_templates.index') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Message Template</a>
                            </li>
                            @permission('create-sms_send*')
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.message_templates.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Message Template</a>
                                </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                    {{-- sending sms --}}
                    @permission('manage-sms_send')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-mail"></i> Sending Messages</a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('biller.send_sms.index') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage SMS</a>
                            </li>
                            @permission('create-sms_send')
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.send_sms.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create SMS</a>
                                </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                    {{-- sending sms --}}
                    @permission('manage-send_email')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-mail"></i> Sending Emails</a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('biller.send_emails.index') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Emails</a>
                            </li>
                            @permission('create-send_email')
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.send_emails.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Email</a>
                                </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                </ul>
            </li>
            @endauth
        </ul>
    </div>
</div>
