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

                    @permission('pos')
                    <li class="nav-item ">
                        <a href="{{ route('biller.invoices.pos') }}" class="btn  btn-success round mt_6">
                            <i class="ficon ft-shopping-cart"></i>{{ trans('pos.pos') }} </a>
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
        <!-- include ../../../includes/mixins-->
        <ul class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="dropdown nav-item">
                <a class="nav-link {{ strpos(Route::currentRouteName(), 'biller.dashboard') === 0 ? 'active' : '' }}"
                   href="{{ route('biller.dashboard') }}"><i
                            class="ft-home"></i><span>{{ trans('navs.frontend.dashboard') }}</span></a>
            </li>

            {{-- customer relation management module --}}
            @if (access()->allow('crm'))
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link"
                                                                      href="#" data-toggle="dropdown"><i
                                class="icon-diamond"></i><span>{{ trans('features.crm') }}</span></a>
                    <ul class="dropdown-menu">
                        {{-- customer --}}
                        @permission('manage-client')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-users"></i></i> {{ trans('labels.backend.customers.management') }}</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.customers.index') }}"
                                       data-toggle="dropdown"><i class="ft-list"></i> Manage Customers
                                    </a>
                                </li>
                                @permission('create-client')
                                <li><a class="dropdown-item" href="{{ route('biller.customers.create') }}"
                                       data-toggle="dropdown"><i class="fa fa-plus-circle"></i>
                                        {{ trans('labels.backend.customers.create') }}
                                    </a>
                                </li>
                                @endauth
                                @permission('create-client')
                                <li><a class="dropdown-item" href="{{ route('biller.customers.aging_report') }}"
                                    data-toggle="dropdown"><i class="ft-list"></i> Aging Report
                                 </a>
                                @endauth
                                
                             </li>
                            </ul>
                        </li>
                        @endauth
                
                        {{-- Client branch --}}
                        @permission('manage-branch')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-users"></i></i> Branch / Site Management</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.branches.index') }}"
                                       data-toggle="dropdown"><i class="ft-list"></i> Manage Branches / Sites
                                    </a>
                                </li>
                                @permission('create-branch')
                                <li><a class="dropdown-item" href="{{ route('biller.branches.create') }}"
                                       data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Branch / Site
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth      
                      
                        {{-- Customer orders --}}
                        @permission('manage-branch')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="fa fa-shopping-cart"></i></i> Orders Management</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.customer_orders.index') }}"
                                       data-toggle="dropdown"><i class="ft-list"></i> Manage Orders
                                    </a>
                                </li>
                                @permission('create-branch')
                                <li><a class="dropdown-item" href="{{ route('biller.customer_orders.create') }}"
                                       data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Orders
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth  

                        {{-- Delivery Schedule --}}
                        @permission('manage-branch')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="fa fa-calendar"></i></i> Delivery Schedule Management</a>
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
                        @endauth       
                        {{-- Delivery --}}
                        @permission('manage-branch')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="fa fa-truck"></i></i> Delivery Management</a>
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
                        @endauth       

                        {{-- Client group --}}
                        @permission('manage-clientgroup')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-grid"></i></i> {{ trans('labels.backend.customergroups.management') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.customergroups.index') }}"
                                       data-toggle="dropdown"><i class="ft-list"></i>
                                        {{ trans('labels.backend.customergroups.management') }}
                                    </a>
                                </li>
                                @permission('create-clientgroup')
                                <li><a class="dropdown-item" href="{{ route('biller.customergroups.create') }}"
                                       data-toggle="dropdown"><i class="fa fa-plus-circle"></i>
                                        {{ trans('labels.backend.customergroups.create') }}
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Client Pricelist --}}
                        @permission('manage-pricelist')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="fa fa-money"></i> Client Pricelist</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.client_products.index') }}"
                                       data-toggle="dropdown"> <i class="ft-list"></i> Manage Pricelist
                                    </a>
                                </li>
                                @permission('create-pricelist')
                                <li><a class="dropdown-item" href="{{ route('biller.client_products.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Pricelist
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- prospect --}}
                        @permission('manage-lead')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-star"></i> Prospects</a>
                            <ul class="dropdown-menu">
                                @permission('manage-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.prospect_questions.index') }}"
                                       data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Prospect Questions</a>
                                </li>
                                @endauth
                                @permission('create-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.prospect_questions.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Prospect Question</a>
                                </li>
                                @endauth
                                @permission('manage-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.leads.index_potential') }}"
                                       data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Ticket Prospects</a>
                                </li>
                                @endauth
                                @permission('manage-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.prospects.index') }}"
                                       data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Prospects</a>
                                </li>
                                @endauth
                                @permission('create-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.prospects.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Prospect</a>
                                </li>
                                @endauth

                                @permission('create-lead')
                                <li><a class="dropdown-item"
                                       href="{{ route('biller.prospectscallresolved.index') }}"
                                       data-toggle="dropdown"> <i class="fa fa-arrow-up"></i> Follow Up</a>
                                </li>
                                @endauth
                                @permission('create-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.calllists.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Call List</a>
                                </li>
                                @endauth
                                @permission('manage-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.calllists.index') }}"
                                       data-toggle="dropdown"> <i class="ft-list"></i> Manage Call List</a>
                                </li>

                                @endauth
                                @permission('manage-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.calllists.mytoday') }}"
                                       data-toggle="dropdown"> <i class="ft-phone"></i> My Today Call List</a>
                                </li>
                                @endauth
                                @permission('manage-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.calllists.previous_call_list') }}"
                                       data-toggle="dropdown"> <i class="ft-phone"></i> Previous Call List</a>
                                </li>
                                @endauth
                                @permission('manage-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.calllists.reasign_call_list') }}"
                                       data-toggle="dropdown"> <i class="ft-phone"></i> Reassign Call List</a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Client Vendor Management --}}
                        @if(auth()->user()->ins == 2)
                            <hr>
                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-users" aria-hidden="true"></i> Client Users</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('biller.client_users.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Users </a></li>

                                    <li><a class="dropdown-item" href="{{ route('biller.client_users.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create User</a></li>

                                </ul>
                            </li>


                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-ship" aria-hidden="true"></i> Vendor Management</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('biller.client_vendors.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Vendors </a></li>

                                    <li><a class="dropdown-item" href="{{ route('biller.client_vendors.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Vendor</a></li>

                                </ul>
                            </li>


                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-sun-o"></i> Ticket Tags</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('biller.client_vendor_tags.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Tags </a></li>

                                    <li><a class="dropdown-item" href="{{ route('biller.client_vendor_tags.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Tag</a></li>

                                </ul>
                            </li>
                        @endif

                        
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-comments-o" aria-hidden="true"></i> Support Tickets</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.client_vendor_tickets.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Support Tickets</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.client_vendor_tickets.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Ticket</a></li>
                            </ul>
                        </li>

                        <!-- AI Agent Leads -->
                        <!-- view if super-acount -->
                        @if (optional(auth()->user()->business)->is_main || access()->allow('crm'))
                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-phone-outgoing"></i> AI Agent Leads</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('biller.agent_leads.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Leads</a></li>
                                    <li><a class="dropdown-item" href="{{ route('biller.agent_leads.omni_transcripts') }}" data-toggle="dropdown"><i class="fa fa-comments" aria-hidden="true"></i> Chat Transcripts</a></li>
                                    <li><a class="dropdown-item" href="{{ route('biller.agent_leads.omni_analytics') }}" data-toggle="dropdown"><i class="fa ft-activity"></i> Chat Analytics</a></li>
                                    <li><a class="dropdown-item" href="{{ route('biller.agent_leads.omni_contacts') }}" data-toggle="dropdown"><i class="fa fa-address-book" aria-hidden="true"></i> Bot Contacts</a></li>
                                    <li><a class="dropdown-item" href="{{ route('biller.omniconvo.media_blocks_index') }}" data-toggle="dropdown"><i class="icon-badge" aria-hidden="true"></i> WhatsApp Media Blocks</a></li>
                                    <li><a class="dropdown-item" href="{{ route('biller.whatsapp_broadcast.index') }}" data-toggle="dropdown"><i class="fa fa-whatsapp" aria-hidden="true"></i> WhatsApp Broadcast</a></li>
                                </ul>
                            </li>
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
                        @endif
                        <!-- End AI Agent Leads -->


                        {{-- Affinite Program --}}
                        @php
                            $affinityPerms = ['manage-promo-codes', 'create-promo-codes', 'manage-reserve-promo-codes', 'create-customer-reservation', 'create-3p-reservation', 'manage-client-feedback'];
                        @endphp
                        @if (access()->allowMultiple($affinityPerms))
                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                                    <i class="ft-check-circle"></i> Referral Program
                                </a>
                                <ul class="dropdown-menu">
                                    {{-- Promo Codes --}}
                                    @permission('manage-promo-codes')
                                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                                            <i class="ft-codepen"></i> Promotional Codes
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item"
                                                href="{{ route('biller.promotional-codes.index') }}"
                                                data-toggle="dropdown"><i class="ft-list"></i> Manage Promo Codes
                                                </a>
                                            </li>
                                            @permission('create-promo-codes')
                                            <li>
                                                <a class="dropdown-item"
                                                href="{{ route('biller.promotional-codes.create') }}"
                                                data-toggle="dropdown"><i class="fa fa-address-book"></i> Create promo Code
                                                </a>
                                            </li>
                                            @endauth
                                        </ul>
                                    </li>
                                    @endauth
                                    {{-- Promo Code Reservations --}}
                                    @permission('manage-reserve-promo-codes')
                                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                                            <i class="fa fa-group"></i> Reserved Promo Codes
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item"
                                                href="{{ route('biller.reserve-promo-codes.index') }}"
                                                data-toggle="dropdown"><i class="ft-list"></i> Manage Reservations
                                                </a>
                                            </li>
                                            @permission('create-customer-reservation')
                                            <li>
                                                <a class="dropdown-item"
                                                href="{{ route('biller.reserve-customer-promo-code') }}"
                                                data-toggle="dropdown"><i class="fa fa-building"></i> Create Customer Reservation
                                                </a>
                                            </li>
                                            @endauth
                                            @permission('create-3p-reservation')
                                            <li>
                                                <a class="dropdown-item"
                                                href="{{ route('biller.reserve-3p-promo-code') }}"
                                                data-toggle="dropdown"><i class="ft-user-plus"></i> Create Third Party Reservation
                                                </a>
                                            </li>
                                            @endauth
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('biller.referrals-index') }}"
                                                   data-toggle="dropdown"><i class="ft-list"></i> Manage Redeemable Codes
                                                </a>
                                            </li>
                                            
                                        </ul>
                                    </li>
                                    @endauth
                                     {{-- Customer Enrollment --}}
                                    @permission('manage-promo-codes')
                                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                                            <i class="ft-codepen"></i> Customer Enrollment
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item"
                                                href="{{ route('biller.customer_enrollments.index') }}"
                                                data-toggle="dropdown"><i class="ft-list"></i> Manage Customer Enrollment
                                                </a>
                                            </li>
                                            @permission('create-promo-codes')
                                            <li>
                                                <a class="dropdown-item"
                                                href="{{ route('biller.customer_enrollments.create') }}"
                                                data-toggle="dropdown"><i class="fa fa-address-book"></i> Create Customer Enrollment
                                                </a>
                                            </li>
                                            @endauth
                                        </ul>
                                    </li>
                                    @endauth
                                    @permission('manage-promo-codes')
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('biller.commissions.internal_commission') }}"
                                                data-toggle="dropdown"><i class="ft-list"></i> Manage Internal Commision
                                            </a>
                                        </li>
                                    @endauth
                                    {{-- Client Feedback --}}
                                    @permission('manage-client-feedback')
                                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                                            <i class="fa fa-exchange"></i> Client Feedback
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item"
                                                href="{{ route('biller.client-feedback.index') }}"
                                                data-toggle="dropdown"><i class="fa fa-list"></i> Manage Client Feedback
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    @endauth
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- sales module --}}
            @if (access()->allow('sale'))
                <li class="dropdown nav-item" data-menu="dropdown">
                    <a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="icon-basket"></i><span>Projects</span></a>                                
                    <ul class="dropdown-menu">
                        {{-- lead --}}
                        @permission('manage-lead')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-phone-outgoing"></i> Tickets / Leads Management</a>
                            <ul class="dropdown-menu">
                                @permission('manage-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.leads.index') }}"
                                       data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Tickets / Leads</a></li>
                                @endauth
                                @permission('create-lead')
                                <li><a class="dropdown-item" href="{{ route('biller.leads.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Ticket / Lead</a>
                                </li>
                                @endauth
                                @permission('manage-lead-sources')
                                <li><a class="dropdown-item" href="{{ route('biller.lead-sources.index') }}"
                                       data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Ticket / Lead Sources</a></li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                        
                        {{-- diagnosis job card --}}
                        @permission('manage-djc')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="icon-tag"></i> Site Survey Report</a>
                            <ul class="dropdown-menu">
                                @permission('manage-djc')
                                <li><a class="dropdown-item" href="{{ route('biller.djcs.index') }}"
                                       data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Site Survey
                                        Report</a></li>
                                @endauth
                                @permission('create-djc')
                                <li><a class="dropdown-item" href="{{ route('biller.djcs.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Report</a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        @permission('manage-tender')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-users"></i></i> Tender Management</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.tenders.index') }}"
                                       data-toggle="dropdown"><i class="ft-list"></i> Manage Tenders
                                    </a>
                                </li>
                                @permission('create-tender')
                                <li><a class="dropdown-item" href="{{ route('biller.tenders.create') }}"
                                       data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Tender
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                        
                        @permission('manage-boqs')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="fa fa-money"></i>Bill of Quantity</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.boqs.index') }}"
                                       data-toggle="dropdown"> <i class="ft-list"></i> Manage Bill of Quantity
                                    </a>
                                </li>
                                @permission('manage-boms')
                                    <li><a class="dropdown-item" href="{{ route('biller.boms.index') }}"
                                        data-toggle="dropdown"> <i class="ft-list"></i> Manage BoM / MTO
                                        </a>
                                    </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                        {{-- quote --}}
                        @permission('manage-quote')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i> Quote / ∑MTO Management</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.quotes.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Quote / ∑MTO </a></li>
                                @permission('create-quote')
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.quotes.create') }}"
                                       data-toggle="dropdown"><i class="fa fa-plus-circle"></i>
                                        Create Quote / ∑MTO</a>
                                    <a class="dropdown-item"
                                       href="{{ route('biller.quotes.create', 'doc_type=maintenance') }}"
                                       data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Maintenance Quote</a>
                                    <a class="dropdown-item" href="{{ route('biller.template-quotes.index') }}"
                                       data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Template Quote</a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('biller.quote_notes.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Quote Note </a></li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                        {{-- proforma-invoice --}}
                        @permission('manage-pi')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-file-text"></i>Proforma Invoice Management</a>
                            <ul class="dropdown-menu">
                                @permission('manage-pi')
                                <li><a class="dropdown-item" href="{{ route('biller.quotes.index', 'page=pi') }}"
                                       data-toggle="dropdown"><i class="ft-list"></i> Manage Proforma Invoice </a>
                                </li>
                                @endauth
                                @permission('create-pi')
                                <li><a class="dropdown-item" href="{{ route('biller.quotes.create', 'page=pi') }}"
                                       data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Proforma
                                        Invoice</a></li>
                                <li><a class="dropdown-item"
                                       href="{{ route('biller.quotes.create', 'page=pi&doc_type=maintenance') }}"
                                       data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Maintenance Proforma
                                        Invoice</a></li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                        
                        {{-- project --}}
                        @permission('manage-project')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-calendar"></i> {{ trans('labels.backend.projects.management') }}</a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.projects.index') }}"
                                       data-toggle="dropdown"><i class="ft-list"></i>Manage
                                        {{ trans('projects.projects') }}</a>
                                </li>
                                @if (!auth()->user()->customer_id)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('biller.miscs.index') }}"
                                        data-toggle="dropdown"><i class="ft-list"></i>Manage Project Tags</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        @endauth
                        {{-- job verification & job valuation --}}
                        @permission('manage-quote-verify')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i> Job Verification / IPC Valuation</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.quotes.verification') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Verifications</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.job_valuations.index') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Quote Partial / IPC Valuation</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.job_valuations.quote_index') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Quote Partial / IPC Valuation</a></li>    
                                <li><a class="dropdown-item" href="{{ route('biller.boq_valuations.index') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Boq Partial / IPC Valuation</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.boq_valuations.boq_index') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Boq Partial / IPC Valuation</a></li>    
                            </ul>
                        </li>
                        @endauth                        
                        
                        {{-- repair job card --}}
                        @permission('manage-rjc')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="icon-tag"></i> Installation/Repair Report</a>
                            <ul class="dropdown-menu">
                                @permission('manage-rjc')
                                <li><a class="dropdown-item" href="{{ route('biller.rjcs.index') }}"
                                       data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage
                                        Installation/Repair Report</a></li>
                                @endauth
                                @permission('create-rjc')
                                <li><a class="dropdown-item" href="{{ route('biller.rjcs.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Report</a>
                                </li>
                                @endauth
                                
                            </ul>
                        </li>
                        @endauth
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                            class="dropdown-item dropdown-toggle" href="{{ route('biller.quotes.turn_around') }}" data-toggle="dropdown"><i
                                class="icon-tag"></i> Turn Around Time</a>
                            
                        </li>
                    </ul>
                </li>
            @endif

            {{-- maintenace project module --}}
            @if (access()->allow('maintenance-project'))
                <li class="dropdown nav-item" data-menu="dropdown">
                    <a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i
                                class="icon-briefcase"></i><span>Maint. & Labour</span></a>
                    <ul class="dropdown-menu">
                        @permission('manage-equipment-category')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="icon-tag"></i> Equipment Category</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.equipmentcategories.index') }}"
                                       data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Categories
                                    </a>
                                </li>
                                @permission('create-equipment-category')
                                <li><a class="dropdown-item" href="{{ route('biller.equipmentcategories.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Category
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                        @permission('manage-equipment')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="icon-tag"></i> Equipment</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.equipments.index') }}"
                                       data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Equipment
                                    </a>
                                </li>
                                @permission('create-equipment')
                                <li><a class="dropdown-item" href="{{ route('biller.equipments.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Equipment
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        @permission('manage-equipment')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="fa-product-hunt"></i>Service Kit
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.toolkits.index') }}"
                                       data-toggle="dropdown"> <i class="ft-list"></i> Manage Service Kits
                                    </a>
                                </li>
{{--                            </ul>--}}

                        </li>
                        @permission('create-equipment')
                        <li><a class="dropdown-item" href="{{ route('biller.toolkits.create') }}"
                               data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Service Kit
                            </a>
                        </li>
                        @endauth
                    </ul>
                </li>
            @endauth

            @permission('manage-pm-contract')
            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                        class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                            class="fa fa-file-text-o"></i>PM Contract Management</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('biller.contracts.index') }}"
                           data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage PM Contracts
                        </a>
                    </li>
                    @permission('create-pm-contract')
                    <li><a class="dropdown-item" href="{{ route('biller.contracts.create') }}"
                           data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create PM Contract
                        </a>
                    </li>
                    <li><a class="dropdown-item" href="{{ route('biller.contracts.create_add_equipment') }}"
                           data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Add PM Equipment
                        </a>
                    </li>
                    @endauth
                </ul>
            </li>
            @endauth

            @permission('manage-schedule')
            <li class="dropdown dropdown-submenu"><a class="dropdown-item" href="#"
                                                     data-toggle="dropdown"> <i class="fa fa-calendar"></i> Schedule Management </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('biller.taskschedules.index') }}"
                           data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Schedule
                        </a>
                    </li>
                    @permission('create-schedule')
                    <li><a class="dropdown-item" href="{{ route('biller.taskschedules.create') }}"
                           data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Load Equipment
                        </a>
                    </li>
                    @endauth
                </ul>
            </li>
            @endauth

            @permission('manage-pm-report')
            <li class="dropdown dropdown-submenu"><a class="dropdown-item" href="#"
                                                     data-toggle="dropdown"> <i class="fa fa-wrench"></i> PM Report Management</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('biller.contractservices.index') }}"
                           data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage PM Report
                        </a>
                    </li>
                    @permission('create-pm-report')
                    <li><a class="dropdown-item" href="{{ route('biller.contractservices.create') }}"
                           data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create PM Report
                        </a>
                    </li>
                    @endauth
                    <li><a class="dropdown-item"
                           href="{{ route('biller.contractservices.serviced_equipment') }}"
                           data-toggle="dropdown"> <i class="icon-tag"></i> Serviced Equipments
                        </a>
                    </li>
                </ul>
            </li>
            @endauth

            @permission('manage-labour_allocation')
            <li class="dropdown dropdown-submenu"><a class="dropdown-item" href="#"
                                                     data-toggle="dropdown"> <i class="fa fa-wrench"></i> Labour Management</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('biller.labour_allocations.index') }}"
                           data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Labour
                        </a>
                    </li>
                    @permission('create-labour_allocation')
                    <li><a class="dropdown-item" href="{{ route('biller.labour_allocations.create') }}"
                           data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Labour
                        </a>
                    </li>
                    <li><a class="dropdown-item"
                           href="{{ route('biller.labour_allocations.employee_summary') }}"
                           data-toggle="dropdown"><i class="fa fa-building" aria-hidden="true"></i> Employee
                            Report
                        </a>
                    </li>
                    @endauth
                </ul>
            </li>
            @endauth         
        </ul>
        </li>
        @endif

        {{-- procurement module --}}
        @if (access()->allow('procurement-management'))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                  data-toggle="dropdown"><i class="fa fa-tags"></i><span>Procurement</span></a>
                <ul class="dropdown-menu">
                    @permission('manage-supplier')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="ft-target"></i> Supplier Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.suppliers.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Suppliers
                                </a>
                            </li>
                            @permission('create-supplier')
                            <li><a class="dropdown-item" href="{{ route('biller.suppliers.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i>
                                    {{ trans('labels.backend.suppliers.create') }}
                                </a>
                            </li>
                            @endauth
                            @permission('create-supplier')
                            <li><a class="dropdown-item" href="{{ route('biller.suppliers.supplier_aging_report') }}"
                                    data-toggle="dropdown"> <i class="ft-list"></i> Aging Report
                                </a>
                            </li>
                            @endauth
                            
                        </ul>
                    </li>
                    @endauth

                    {{-- Supplier Pricelist --}}
                    @permission('manage-pricelist')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-money"></i> Supplier Pricelist</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.pricelistsSupplier.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Pricelist
                                </a>
                            </li>
                            @permission('create-pricelist')
                            <li><a class="dropdown-item" href="{{ route('biller.pricelistsSupplier.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Pricelist
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                     {{-- Manage Purchases --}}
                     @permission('manage-purchase')
                     <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                         <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                             <i class="ft-file-text"></i> Expense Management
                         </a>
                         <ul class="dropdown-menu">
                             <li><a class="dropdown-item" href="{{ route('biller.purchases.index') }}"
                                    data-toggle="dropdown"> <i class="ft-list"></i> Manage Expenses
                                 </a>
                             </li>
                             @permission('create-purchase')
                             <li><a class="dropdown-item" href="{{ route('biller.purchases.create') }}"
                                    data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Expense
                                 </a>
                             </li>
                             @endauth
                         </ul>
                     </li>
                     @endauth
                     

                    {{-- Purchase Requisition Management --}}
                    @permission('manage-product')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-cube"></i> Material Requisition Management</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.purchase_requests.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Material Requisition
                                </a>
                            </li>
                            @permission('create-product')
                            <li><a class="dropdown-item" href="{{ route('biller.purchase_requests.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Material Requisition
                                </a>
                            </li>
                            @endauth
                            {{-- <li><a class="dropdown-item" href="{{ route('biller.queuerequisitions.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage QueueRequisition
                                </a>
                            </li> --}}
                        </ul>
                    </li>
                    @endauth
                    {{-- Purchase Requisition Management --}}
                    @permission('manage-purchase_requisition')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-cube"></i> Purchase Requisition Management</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.purchase_requisitions.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i>Purchase Manage Requisition
                                </a>
                            </li>
                            @permission('create-purchase_requisition')
                            <li><a class="dropdown-item" href="{{ route('biller.purchase_requisitions.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Purchase Requisition
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-rfq')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-money"></i> Request For Quotation</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.rfq.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Request For Quotations
                                </a>
                            </li>
                            @permission('create-rfq')
                            <li><a class="dropdown-item" href="{{ route('biller.rfq.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Request For Quotations
                                </a>
                            </li>
                            @endauth
                            <li><a class="dropdown-item" href="{{ route('biller.rfq_analysis.index') }}"
                                data-toggle="dropdown"> <i class="ft-list"></i> Manage RfQ Analysis
                             </a>
                         </li>
                        </ul>
                    </li>
                    @endauth
                   
                    

                    @permission('manage-purchase')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="ft-file-text"></i> Purchase Order Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.purchaseorders.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Purchase Orders
                                </a>
                            </li>
                            @permission('create-purchase')
                            <li><a class="dropdown-item" href="{{ route('biller.purchaseorders.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Purchase Order
                                </a>
                            </li>
                            @endauth
                            <li><a class="dropdown-item" href="{{ route('biller.purchaseorders.index_review') }}"
                                data-toggle="dropdown"> <i class="ft-list"></i> Manage LPO Reviews
                             </a>
                         </li>
                        </ul>
                    </li>
                    @endauth

                    {{-- Manage Import Requests --}}
                    @permission('manage-import_request')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                            <i class="ft-file-text"></i> Import Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.import_requests.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Imports
                                </a>
                            </li>
                            @permission('create-import_request')
                            <li><a class="dropdown-item" href="{{ route('biller.import_requests.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Import
                                </a>
                            </li>
                            @endauth

                            {{-- Manage SP Costing --}}
                            @permission('manage-import_request')
                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                                    <i class="ft-file-text"></i> SP Costing Management
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('biller.sell_prices.index') }}"
                                        data-toggle="dropdown"> <i class="ft-list"></i> Manage SP Costing
                                        </a>
                                    </li>
                                    @permission('create-import_request')
                                    <li><a class="dropdown-item" href="{{ route('biller.sell_prices.create') }}"
                                        data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create SP Costing
                                        </a>
                                    </li>
                                    @endauth
                                </ul>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    

                    @permission('manage-purchase-class')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-map-pin"></i></i> Non-Project Expense Class</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item"
                                   href="{{ route('biller.purchase-classes.index') }}"
                                   data-toggle="dropdown"><i class="fa fa-list-alt"></i> Manage Expense Classes
                                </a>
                            </li>
                            @permission('create-purchase-class')
                            <li><a class="dropdown-item"
                                   href="{{ route('biller.purchase-classes.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus"></i>  Create Expense Class
                                </a>

                            </li>
                            @endauth


                            @permission('manage-expense-category')
                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                        class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                            class="fa fa-list"></i></i> Expense Category </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item"
                                           href="{{ route('biller.expense-category.index') }}"
                                           data-toggle="dropdown"><i class="fa fa-list-alt"></i> Manage Expense Categories
                                        </a>
                                    </li>
                                    @permission('create-expense-category')
                                    <li><a class="dropdown-item"
                                           href="{{ route('biller.expense-category.create') }}"
                                           data-toggle="dropdown"><i class="fa fa-plus"></i>Create Expense Category
                                        </a>

                                    </li>
                                    @endauth
                                </ul>
                            </li>
                            @endauth

                        </ul>
                    </li>
                    @endauth

                    @permission('manage-purchase-class-budget')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-paper-plane"></i></i> Non-Project Class Budgets</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item"
                                   href="{{ route('biller.purchase-class-budgets.index') }}"
                                   data-toggle="dropdown"><i class="fa fa-list-alt"></i> Manage Budgets
                                </a>
                            </li>
                            @permission('create-purchase-class-budget')
                            <li><a class="dropdown-item"
                                   href="{{ route('biller.purchase-class-budgets.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus"></i>Create Budget
                                </a>

                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth


                    @permission('manage-financial-year')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-newspaper-o"></i> Budget Financial Year
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.financial_years.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Financial Years
                                </a>
                            </li>
                            @permission('create-financial-year')
                            <li><a class="dropdown-item" href="{{ route('biller.financial_years.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Financial Year
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                </ul>
            </li>
        @endif

        {{-- inventory module --}}
        @if(access()->allow('stock'))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="ft-layers"></i><span>Inventory</span></a>
                <ul class="dropdown-menu">
                     {{-- Goods Receive Note --}}
                     @permission('manage-grn')
                     <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                         <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-puzzle-piece"></i> Goods Receive Note</a>
                         <ul class="dropdown-menu">
                             <li><a class="dropdown-item" href="{{ route('biller.goodsreceivenote.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage GRN
                                 </a>
                             </li>
                             @permission('create-grn')
                             <li><a class="dropdown-item" href="{{ route('biller.goodsreceivenote.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create GRN
                                 </a>
                             </li>
                             @endauth
                         </ul>
                     </li>
                     @endauth
 
 
                     {{-- Project-stock issuance --}}
                      @permission('manage-issuance')
                     <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                         <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-cubes" aria-hidden="true"></i> Stock Issuing</a>
                         <ul class="dropdown-menu">
                             <li><a class="dropdown-item" href="{{ route('biller.stock_issues.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Stock Issues</a></li>
                              @permission('create-issuance')
                             <li><a class="dropdown-item" href="{{ route('biller.stock_issues.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Stock Issue</a></li>
                              @endauth
                         </ul>
                     </li>
                      @endauth
 
                      {{-- Sale Returns --}}
                     <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                         <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-money"></i> Sale Returns</a>
                         <ul class="dropdown-menu">
                             <li>
                                 <a class="dropdown-item" href="{{ route('biller.sale_returns.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Sale Returns</a>
                             </li>
                             <li>
                                 <a class="dropdown-item" href="{{ route('biller.sale_returns.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Sale Return</a>
                             </li>
                         </ul>
                     </li>
 
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

                      @permission('manage-approved-budgets')
                      <li><a class="dropdown-item" href="{{ route('biller.quotes-approved-budgets') }}"
                             data-toggle="dropdown"><i class="ft-paperclip"></i> Approved Budget Prints </a></li>
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
                            @if (config('services.efris.base_url'))
                                <li><a class="dropdown-item" href="{{ route('biller.products.efris_goods_config') }}"> <i class="fa fa-list-ol"></i> EFRIS Commodity Assigning</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.products.efris_goods_upload_view') }}"> <i class="fa fa-cloud-upload"></i> EFRIS Goods Upload</a></li>
                            @endif
                        </li>
                        </ul>
                    </li>
                    @endauth

                    {{-- Product Category --}}
                    @permission('manage-product-category')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-object-ungroup"></i> Product Categories / UoM
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

                    {{-- Product Standard Template --}}
                   
                    {{-- Product Parts --}}
                    @permission('manage-part')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-object-ungroup"></i> Finished Goods Management
                        </a>
                        <ul class="dropdown-menu">
                            @permission('manage-part')
                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="icon-briefcase"></i> FG Template
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('biller.standard_templates.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> FG Template Management
                                        </a>
                                    </li>
                                    @permission('create-part')
                                    <li><a class="dropdown-item" href="{{ route('biller.standard_templates.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create FG Template
                                        </a>
                                    </li>
                                    @endauth
                                </ul>
                            </li>
                            @endauth
                            <li><a class="dropdown-item" href="{{ route('biller.parts.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Finished Goods
                                </a>
                            </li>
                            @permission('create-part')
                            <li><a class="dropdown-item" href="{{ route('biller.parts.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Finished Goods
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    

                    {{-- Asset and Equipments  --}}
                    @permission('manage-asset-equipment')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-target"></i> Assets & Equipments
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.assetequipments.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Assets & Equipments Management
                                </a>
                            </li>
                            @permission('create-asset-equipment')
                            <li><a class="dropdown-item" href="{{ route('biller.assetequipments.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Assets & Equipments
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                </ul>
            </li>
        @endif


        {{-- finance module --}}
        @if (access()->allow('finance-management'))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                  data-toggle="dropdown"><i
                            class="icon-calculator"></i><span>{{ trans('general.finance') }}</span></a>
                <ul class="dropdown-menu">
                    @permission('manage-client-lpo')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="ft-file-text"></i> Client LPO</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.lpo.index') }}"
                                   data-toggle="dropdown"><i class="ft-list"></i> Manage Client LPO</a></li>
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-bill')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="ft-layout"></i> Bills Management</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.utility_bills.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Bills</a></li>
                            <li><a class="dropdown-item" href="{{ route('biller.supplier-outstanding.index') }}" data-toggle="dropdown"><i class="fa fa-money"></i> Supplier Balances </a></li>
                            @permission('create-bill')
                            <li><a class="dropdown-item" href="{{ route('biller.utility_bills.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i>Create Bill</a></li>
                            <li><a class="dropdown-item" href="{{ route('biller.utility_bills.create_kra_bill') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i>Create KRA Bill</a> </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-invoice')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-usd"></i> Invoice Management</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('biller.invoices.index') }}" data-toggle="dropdown"><i class="ft-file-text"></i> Manage Invoices</a></li>
                        <li><a class="dropdown-item" href="{{ route('biller.invoices.sales_variance') }}" data-toggle="dropdown"><i class="ft-file-text"></i> Sales Variance Report</a></li>
                        <li><a class="dropdown-item" href="{{ route('biller.invoices.ipc_retention') }}" data-toggle="dropdown"><i class="ft-file-text"></i> DLP & Moiety Retention Tracker</a></li>
                        <li><a class="dropdown-item" href="{{ route('biller.client-balances.index') }}" data-toggle="dropdown"><i class="fa fa-money"></i> Customer Balances </a></li>
                            @permission('create-invoice')
                                <li><a class="dropdown-item" href="{{ route('biller.invoices.uninvoiced_quote') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Project Invoice</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.standard_invoices.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Detached Invoice</a></li>
                            @endauth
                            @permission('manage-cu-invoice-number')
                                <li><a class="dropdown-item" href="{{ route('biller.control-unit-invoice-number.index') }}" data-toggle="dropdown"><i class="ft-file-text"></i> Manage CU Invoice Number </a></li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-credit-note')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-money"></i>Customer Credit Note</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.creditnotes.index') }}"
                                   data-toggle="dropdown"><i class="ft-list"></i>
                                    Manage Customer Credit Note
                                </a>
                            </li>
                            @permission('create-credit-note')
                            <li><a class="dropdown-item" href="{{ route('biller.creditnotes.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i>
                                    Create Customer Credit Note
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                    @permission('manage-credit-note')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-money"></i>Supplier Credit Note</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.supplier_creditnotes.index') }}"
                                   data-toggle="dropdown"><i class="ft-list"></i>
                                    Manage Supplier Credit Note
                                </a>
                            </li>
                            @permission('create-credit-note')
                            <li><a class="dropdown-item" href="{{ route('biller.supplier_creditnotes.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i>
                                    Create Supplier Credit Note
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-debit-note')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-money"></i> Debit Note</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.creditnotes.index') }}?is_debit=1"
                                   data-toggle="dropdown"><i class="ft-list"></i> Manage Debit Notes
                                </a>
                            </li>
                            @permission('create-debit-note')
                            <li><a class="dropdown-item" href="{{ route('biller.creditnotes.create') }}?is_debit=1"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Debit Note
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth


                    @permission('manage-journal')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-newspaper-o"></i> Manual Journals
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.journals.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Manual Journals
                                </a>
                            </li>
                            @permission('create-journal')
                            <li><a class="dropdown-item" href="{{ route('biller.journals.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Manual Journal
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-account')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-book"></i> Charts Of Accounts
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.accounts.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Accounts
                                </a>
                            </li>
                            @permission('create-account')
                            <li><a class="dropdown-item" href="{{ route('biller.accounts.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Account
                                </a>
                            </li>
                            @endauth
                            {{-- <li><a class="dropdown-item" href="{{ route('biller.transactions.index') }}"
                                   data-toggle="dropdown"> <i class="fa fa-exchange"></i> Double Entry Transactions
                                </a>
                            </li> --}}
                            <li><a class="dropdown-item" href="{{ route('biller.accounts.general_ledger') }}"
                                data-toggle="dropdown"> <i class="fa fa-exchange"></i> General Ledger
                             </a>
                         </li>
                        </ul>
                    </li>

                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-book"></i>Accounting Reports</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.trial_balance', 'v') }}"><i class="fa fa-balance-scale"></i> Trial Balance</a></li>
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.profit_and_loss', 'v') }}"><i class="fa fa-money"></i> Profit & Loss</a></li>
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.balance_sheet', 'v') }}"><i class="fa fa-book"></i> {{ trans('accounts.balance_sheet') }}</a></li>
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.cash_flow_statement', 'v') }}"><i class="fa fa-money"></i> Cash Flow Statement</a></li>
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.cashbook') }}"><i class="fa fa-book"></i>Cashbook Statement</a></li>
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.project_gross_profit') }}"><i class="fa fa-money"></i>Project Gross Profit</a></li>                                            
                        </ul>
                    </li>

                    {{-- Tax Report --}}
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-balance-scale"></i> Tax Returns</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.tax_reports.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Tax Returns</a></li>
                            <li><a class="dropdown-item" href="{{ route('biller.tax_reports.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Tax Return</a></li>
                            <li><a class="dropdown-item " href="{{ route('biller.tax_reports.filed_report') }}"><i class="fa fa-book"></i> Filed Tax Returns</a></li>
                                @permission('manage-withholding-cert')
                                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                            class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                                class="fa fa-file"></i>WH. Certificates
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('biller.withholdings.index') }}"
                                            data-toggle="dropdown"> <i class="ft-list"></i>Manage WH. Certificates
                                            </a>
                                        </li>
                                        @permission('create-withholding-cert')
                                        <li><a class="dropdown-item" href="{{ route('biller.withholdings.create') }}"
                                            data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create WH. Certificate
                                            </a>
                                        </li>
                                        @endauth
                                    </ul>
                                </li>
                                @endauth
                            </li>
                            {{-- Tax PRN --}}
                                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                        class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                            class="fa fa-check-square-o"></i> Return Acknowledgement</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('biller.tax_prns.index') }}"
                                        data-toggle="dropdown"> <i class="ft-list"></i> Manage Return Acknowledgement</a>
                                    </li>
                                    <li><a class="dropdown-item" href="{{ route('biller.tax_prns.create') }}"
                                        data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Return
                                            Acknowledgement</a></li>
                                </ul>
                            </li>
                        </ul>
                    
                    @endauth
                </ul>
            </li>
        @endif

        {{-- banking module --}}
        @if (access()->allow('banking-management'))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                  data-toggle="dropdown"><i class="fa fa-bank"></i><span>Banking</span></a>
                <ul class="dropdown-menu">
                    @permission('manage-bill')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="ft-layout"></i> Bill Payment</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.billpayments.index') }}"><i
                                            class="fa fa-money"></i> Manage Payments</a></li>
                            @permission('create-bill')
                            <li><a class="dropdown-item" href="{{ route('biller.billpayments.create') }}"><i
                                            class="fa fa-plus-circle"></i> Make Payment</a></li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    {{-- Manage Petty cash --}}
                    @permission('manage-petty_cash')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">
                            <i class="ft-file-text"></i> Petty Cash Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.petty_cashs.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Petty Cash
                                </a>
                            </li>
                            @permission('create-petty_cash')
                            <li><a class="dropdown-item" href="{{ route('biller.petty_cashs.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Petty Cash
                                </a>
                            </li>
                            @endauth
                            <li><a class="dropdown-item" href="{{ route('biller.petty_cashs.index_petty_cash') }}"
                                   data-toggle="dropdown"> <i class="fa fa fa-money"></i> Petty Cash Report
                                </a>
                            </li>
                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                            class="fas fa-hotel"></i> Third Party Users Management</a>
                                <ul class="dropdown-menu">
                                    @permission('manage-department')
                                    <li><a class="dropdown-item" href="{{ route('biller.third_party_users.index') }}"
                                        data-toggle="dropdown"> <i class="ft-list"></i> Manage Third Party Users
                                        </a>
                                    </li>
                                    @endauth
                                    @permission('create-department')
                                    <li><a class="dropdown-item" href="{{ route('biller.third_party_users.create') }}"
                                        data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Third Party User
                                        </a>
                                    </li>
                                    @endauth
                                </ul>
                            </li>
                        </ul>
                    </li>
                    @endauth

                     @permission('manage-commission')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-exchange"></i> Commission Payment
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.commissions.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i>Manage Commissions Payment
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-invoice')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="ft-layout"></i> Invoice Payment</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.invoice_payments.index') }}"><i
                                            class="fa fa-money"></i> Manage Payments</a></li>
                            @permission('create-invoice')
                            <li><a class="dropdown-item" href="{{ route('biller.invoice_payments.create') }}"><i
                                            class="fa fa-plus-circle"></i> Receive Payment</a></li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-money-transfer')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-exchange"></i> Money Transfer
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.banktransfers.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i>Manage Transfer
                                </a>
                            </li>
                            @permission('create-money-transfer')
                            <li><a class="dropdown-item" href="{{ route('biller.banktransfers.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Transfer
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-account-charge')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-money"></i> Account Charges
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.charges.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Account Charges
                                </a>
                            </li>
                            @permission('create-account-charge')
                            <li><a class="dropdown-item" href="{{ route('biller.charges.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Account Charges
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-reconciliation')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-handshake-o"></i>Reconciliation
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.reconciliations.index') }}"
                                   data-toggle="dropdown"><i class="ft-list"></i>Manage Reconciliations
                                </a>
                            </li>
                            @permission('create-reconciliation')
                            <li><a class="dropdown-item" href="{{ route('biller.reconciliations.create') }}"
                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Reconciliation
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    <!-- Bank Feeds -->
                    <li><a class="dropdown-item" href="{{ route('biller.bank_feeds.index') }}"><i class="fa fa-balance-scale"></i> Bank Feeds</a></li>
                </ul>
            </li>
        @endif


        {{-- human resource module --}}
        @if (access()->allow('hrm'))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link"
                                                                  href="#" data-toggle="dropdown"><i
                            class="icon-badge"></i><span>{{ trans('features.hrm') }}</span></a>
                <ul class="dropdown-menu">
                    @permission('manage-department')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-users"></i> User Access Management </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.hrms.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i>
                                    {{ trans('hrms.employees') }}</a>
                            </li>
                            @permission('create-department')
                            <li><a class="dropdown-item" href="{{ route('biller.hrms.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i>
                                    {{ trans('hrms.create') }}
                                </a>
                            </li>
                            @endauth


                            @permission('view-stakeholders')
                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                            class="fa fa-book"></i> Stakeholder Access </a>
                                <ul class="dropdown-menu">
                                    @permission('view-stakeholders')
                                    <li><a class="dropdown-item" href="{{ route('biller.stakeholders.index') }}"
                                           data-toggle="dropdown"> <i class="ft-list"></i> Manage Stakeholders
                                        </a>
                                    </li>
                                    @endauth
                                    @permission('create-stakeholders')
                                    <li><a class="dropdown-item" href="{{ route('biller.stakeholders.create') }}"
                                           data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Stakeholder
                                        </a>
                                    </li>
                                    @endauth
                                </ul>
                            </li>
                            @endauth


                            <li><a class="dropdown-item" href="{{ route('biller.role.index') }}"
                                   data-toggle="dropdown"> <i class="ft-pocket"></i>
                                    {{ trans('hrms.roles') }}</a>
                            </li>

                        </ul>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('biller.departments.index') }}"
                           data-toggle="dropdown"> <i class="ft-list"></i>
                            {{ trans('departments.departments') }}</a>
                    </li>
                    @endauth

                    @permission('manage-employee-appraisal')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="fa fa-paperclip"></i>Employee Appraisals</a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.appraisal_types.index') }}"
                                       data-toggle="dropdown"> <i class="ft-list"></i> Manage Appraisal Type
                                    </a>
                                </li>
                                @permission('create-employee-appraisal')
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.appraisal_types.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Appraisal Type
                                    </a>
                                </li>
                                @endauth
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.employee_appraisals.index') }}"
                                       data-toggle="dropdown"> <i class="ft-list"></i> Manage Appraisal
                                    </a>
                                </li>
                                @permission('create-employee-appraisal')
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.employee_appraisals.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Appraisal
                                    </a>
                                </li>
                                @endauth
                                @permission('manage-employee-notice')
                                <li><a class="dropdown-item" href="{{ route('biller.employee-notice.index') }}"
                                       data-toggle="dropdown"> <i class="ft-list"></i> Manage Employee Notice
                                    </a>
                                </li>
                                @endauth
                                @permission('create-employee-notice')
                                <li><a class="dropdown-item" href="{{ route('biller.employee-notice.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i>
                                        Create Employee Notice
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                    @endauth


                    @permission('manage-holiday')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fas fa-hotel"></i> Holiday Management</a>
                        <ul class="dropdown-menu">
                            @permission('manage-holiday')
                            <li><a class="dropdown-item" href="{{ route('biller.holiday_list.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Holiday
                                </a>
                            </li>
                            @endauth
                            @permission('create-holiday')
                            <li><a class="dropdown-item" href="{{ route('biller.holiday_list.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Holiday
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth


                    @permission('manage-holiday')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-briefcase "></i> Job Title Management</a>
                        <ul class="dropdown-menu">
                            @permission('manage-holiday')
                            <li><a class="dropdown-item" href="{{ route('biller.jobtitles.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Jobtitle
                                </a>
                            </li>
                            @endauth
                            @permission('create-holiday')
                            <li><a class="dropdown-item" href="{{ route('biller.jobtitles.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Jobtitle
                                </a>
                            </li>
                            @endauth

                            @permission('manage-job-grades')
                            <li>
                                <a class="dropdown-item" href="{{ route('biller.job-grades.index') }}"
                                   data-toggle="dropdown"> <i class="fa fa-flag"></i> Job Grades
                                </a>
                            </li>
                            @endauth

                        </ul>
                    </li>
                    @endauth
                    @permission('manage-holiday')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fas fa-hotel"></i> WorkShift Management</a>
                        <ul class="dropdown-menu">
                            @permission('manage-holiday')
                            <li><a class="dropdown-item" href="{{ route('biller.workshifts.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage WorkShift
                                </a>
                            </li>
                            @endauth
                            @permission('create-holiday')
                            <li><a class="dropdown-item" href="{{ route('biller.workshifts.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create WorkShift
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                   @permission('manage-leave')
                   <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                       <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                   class="fas fa-hotel"></i> Leave Category</a>
                       <ul class="dropdown-menu">
                           <li><a class="dropdown-item" href="{{ route('biller.leave_category.index') }}"
                                  data-toggle="dropdown"> <i class="ft-list"></i> Manage Category
                               </a>
                           </li>
                           @permission('create-leave')
                           <li><a class="dropdown-item" href="{{ route('biller.leave_category.create') }}"
                                  data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Category
                               </a>
                           </li>
                           @endauth
                       </ul>
                   </li>
                   @endauth


                   @permission('manage-leave-application')
                   <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                       <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                   class="fas fa-hotel"></i> Leave Application</a>
                       <ul class="dropdown-menu">
                           <li><a class="dropdown-item" href="{{ route('biller.leave.index') }}"
                                  data-toggle="dropdown"> <i class="ft-list"></i> Manage Leave Application
                               </a>
                           </li>
                           @permission('create-leave-application')
                           <li><a class="dropdown-item" href="{{ route('biller.leave.create') }}"
                                  data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Leave
                               </a>
                           </li>
                           @endauth
                       </ul>
                   </li>
                   @endauth

                   @permission('manage-attendance')
                   <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                               class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                   class="fa ft-activity"></i> {{ trans('hrms.attendance') }}</a>
                       <ul class="dropdown-menu">
                           <li><a class="dropdown-item" href="{{ route('biller.attendances.index') }}"
                                  data-toggle="dropdown"> <i class="ft-list"></i> Manage
                                   {{ trans('attendances') }}
                               </a>
                           </li>
                           @permission('create-attendance')
                           <li><a class="dropdown-item" href="{{ route('biller.attendances.create') }}"
                                  data-toggle="dropdown"> <i class="fa fa-plus-circle"></i>
                                   {{ trans('hrms.attendance_add') }}
                               </a>
                           </li>
                           @endauth
                       </ul>
                   </li>
                   @endauth

                   {{-- @permission('manage-employee-notice')
                   <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                               class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                   class="fa ft-paperclip"></i> Employee Notice </a>
                       <ul class="dropdown-menu">
                           
                       </ul>
                   </li>
                   @endauth --}}

{{--                    @permission('manage-loan')--}}
{{--                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a--}}
{{--                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i--}}
{{--                                    class="fa fa-briefcase"></i>Loan Management--}}
{{--                        </a>--}}
{{--                        <ul class="dropdown-menu">--}}
{{--                            <li><a class="dropdown-item" href="{{ route('biller.loans.index') }}"--}}
{{--                                   data-toggle="dropdown"> <i class="ft-list"></i>Manage Loans--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            @permission('create-loan')--}}
{{--                            <li><a class="dropdown-item" href="{{ route('biller.loans.create') }}"--}}
{{--                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Loan--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            @endauth--}}
{{--                            <li><a class="dropdown-item" href="{{ route('biller.loans.pay_loans') }}"--}}
{{--                                   data-toggle="dropdown"> <i class="fa fa-money"></i> Pay Loans--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                    @endauth--}}

                    @permission('manage-advance-payment')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-money"></i> Advance Payment</a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('biller.advance_payments.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Payments</a>
                            </li>
                            @permission('create-advance-payment')
                            <li>
                                <a class="dropdown-item" href="{{ route('biller.advance_payments.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Payment </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-payroll')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-money"></i>Payroll</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.payroll.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Payroll
                                </a>
                            </li>
                            @permission('create-payroll')
                            <li><a class="dropdown-item" href="{{ route('biller.payroll.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Payroll
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                    @permission('manage-salary')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-money"></i>Set Salary</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.salary.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Salary
                                </a>
                            </li>
                            @permission('create-salary')
                                <li><a class="dropdown-item" href="{{ route('biller.salary.create') }}"
                                       data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Salary
                                    </a>
                                </li>
                            @endauth

                        </ul>
                    </li>
                    @endauth

                    @permission('manage-casuals')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i> Casuals Management</a>
                        <ul class="dropdown-menu">
                            @permission('manage-casuals')
                            <li><a class="dropdown-item" href="{{ route('biller.casuals.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Casuals
                                </a>
                            </li>
                            @endauth
                            @permission('create-casuals')
                            <li><a class="dropdown-item" href="{{ route('biller.casuals.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Casuals
                                </a>
                            </li>
                            @endauth
                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i> Job Categories</a>
                                <ul class="dropdown-menu">                          
                                    @permission('manage-casuals')
                                    <li><a class="dropdown-item" href="{{ route('biller.job-categories.index') }}"
                                           data-toggle="dropdown"> <i class="ft-list"></i> Manage Job Categories
                                        </a>
                                    </li>
                                    @endauth
                                    @permission('create-casuals')
                                    <li><a class="dropdown-item" href="{{ route('biller.job-categories.create') }}"
                                           data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Job Category
                                        </a>
                                    </li>
                                    @endauth                           
                                </ul>
                            </li>

                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i> Wage Items</a>
                                <ul class="dropdown-menu">                            
                                    <li><a class="dropdown-item" href="{{ route('biller.wage_items.index') }}"
                                           data-toggle="dropdown"> <i class="ft-list"></i> Manage Wage Items
                                        </a>
                                    </li>                            
                                    <li><a class="dropdown-item" href="{{ route('biller.wage_items.create') }}"
                                           data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Wage Item
                                        </a>
                                    </li>                            
                                </ul>
                            </li>
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-casual-labourers-remuneration')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                    class="fa fa-address-book"></i> Casuals Remuneration</a>
                        <ul class="dropdown-menu">

                            <li><a class="dropdown-item" href="{{ route('biller.casual_remunerations.index') }}"
                                   data-toggle="dropdown"> <i class="ft-list"></i> Manage Remunerations
                                </a>
                            </li>

                            @permission('manage-casual-labourers-remuneration')
                            <li><a class="dropdown-item" href="{{ route('biller.casual_remunerations.create') }}"
                                   data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Remuneration
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth   
                </ul>
            </li>
        @endif

        @permission('manage-daily-logs')
        <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="icon-clock"></i><span>Daily Logs</span></a>
            <ul class="dropdown-menu">
                @if(access()->allow('view-calendar-events'))
                    <li>
                        <a class="dropdown-item" href="{{ route('biller.calendar.index') }}"
                           data-toggle="dropdown"><i class="ft-calendar"></i> Calendar
                        </a>
                    </li>
                @endif
                
                @permission('manage-daily-logs')
                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                            class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                class="icon-clock"></i></i> KPI Logs </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('biller.employee-daily-log.index') }}"
                               data-toggle="dropdown"><i class="ft-list"></i> Manage
                            </a>
                        </li>
                        @permission('create-daily-logs')
                        <li><a class="dropdown-item" href="{{ route('biller.employee-daily-log.create') }}"
                               data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create
                            </a>
                        </li>
                        <li><a class="dropdown-item"
                               href="{{ route('biller.edl-subcategory-allocations.allocations') }}"
                               data-toggle="dropdown"><i class="icon-note"></i> My KPI's
                            </a>
                        </li>
                        <li><a class="dropdown-item"
                               href="{{ route('biller.employee-daily-log.index_kpis') }}"
                               data-toggle="dropdown"><i class="ft-layers"></i> My KPI Report
                            </a>
                        </li>
                        @endauth
                    </ul>
                </li>
                @endauth

                @permission('allocate-edl-categories')
                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                            class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                class="ft-grid"></i></i> KPI Log Tasks Allocation
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                               href="{{ route('biller.edl-subcategory-allocations.index') }}"
                               data-toggle="dropdown"><i class="ft-list"></i> Manage
                            </a>
                        </li>
                    </ul>
                </li>
                @endauth

                @permission('manage-edl-categories')
                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                            class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                class="ft-bookmark"></i></i> KPI Log Tasks</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                               href="{{ route('biller.employee-task-subcategories.index') }}"
                               data-toggle="dropdown"><i class="ft-list"></i> Manage
                            </a>
                        </li>
                        @permission('create-edl-categories')
                        <li><a class="dropdown-item"
                               href="{{ route('biller.employee-task-subcategories.create') }}"
                               data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create
                            </a>

                        </li>
                        @endauth
                        @permission('create-key_activity')
                        <li><a class="dropdown-item"
                               href="{{ route('biller.key_activities.index') }}"
                               data-toggle="dropdown"><i class="ft-list"></i>Manage Key Activity
                            </a>

                        </li>
                        <li><a class="dropdown-item"
                               href="{{ route('biller.key_activities.create') }}"
                               data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Key Activity
                            </a>

                        </li>
                        @endauth
                    </ul>
                </li>
                @endauth


                {{-- Health and Safety Tracking --}}
                @permission('manage-health-safety-tracking')

                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                            class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                class="ft-heart"></i></i> Health and Safety Tracking</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                               href="{{ route('biller.health-and-safety.index') }}"
                               data-toggle="dropdown"><i class="ft-list"></i> Manage Health and Safety
                            </a>
                        </li>
                        @permission('create-health-safety-tracking')
                        <li><a class="dropdown-item"
                               href="{{ route('biller.health-and-safety.create') }}"
                               data-toggle="dropdown"><i class="ft-plus-circle"></i>Create Health and Safety Entry
                            </a>

                        </li>
                        @endauth
                    </ul>
                </li>
                @endauth

                @permission('manage-quality-tracking')
                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                            class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                class="ft-check-circle"></i></i> Quality Tracking</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                               href="{{ route('biller.quality-tracking.index') }}"
                               data-toggle="dropdown"><i class="ft-list"></i> Manage Quality Tracking
                            </a>
                        </li>
                        @permission('create-quality-tracking')
                        <li><a class="dropdown-item"
                               href="{{ route('biller.quality-tracking.create') }}"
                               data-toggle="dropdown"><i class="ft-plus-circle"></i>Create Quality Tracking
                            </a>

                        </li>
                        @endauth
                    </ul>
                </li>
                @endauth


                @permission('manage-environmental-tracking')
                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                            class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                class="ft-check-circle"></i></i> Environmental Tracking</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                               href="{{ route('biller.environmental-tracking.index') }}"
                               data-toggle="dropdown"><i class="ft-list"></i> Manage Environmental Tracking
                            </a>
                        </li>
                        @permission('create-environmental-tracking')
                        <li><a class="dropdown-item"
                               href="{{ route('biller.environmental-tracking.create') }}"
                               data-toggle="dropdown"><i class="ft-plus-circle"></i>Create Environmental Tracking
                            </a>

                        </li>
                        @endauth
                    </ul>
                </li>
                @endauth


                @permission('manage-document-tracker')
                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                            class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                class="fa fa-paperclip"></i></i> Document Expiry Tracker</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                               href="{{ route('biller.document-tracker.index') }}"
                               data-toggle="dropdown"><i class="fa fa-list-alt"></i> Manage Document Trackers
                            </a>
                        </li>
                        @permission('create-document-tracker')
                        <li><a class="dropdown-item"
                               href="{{ route('biller.document-tracker.create') }}"
                               data-toggle="dropdown"><i class="fa fa-plus"></i>Create a Document Tracker
                            </a>

                        </li>
                        @endauth
                    </ul>
                </li>
                @endauth

                @permission('manage-company-notice-board')
                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                            class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                class="fa fa-hand-paper-o"></i></i> Company Notice Board</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                               href="{{ route('biller.company-notice-board.index') }}"
                               data-toggle="dropdown"><i class="fa fa-list-alt"></i> View Company Notice Board
                            </a>
                        </li>
                        @permission('create-company-notice-board')
                        <li><a class="dropdown-item"
                               href="{{ route('biller.company-notice-board.create') }}"
                               data-toggle="dropdown"><i class="fa fa-plus"></i>Add to Company Notice Board
                            </a>

                        </li>
                        @endauth
                    </ul>
                </li>
                @endauth

                {{-- customer complaints --}}
                @permission('manage-customer-complaints')
                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                    <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i> Customer Complaints</a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('biller.customer_complains.index') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Customer Complaints</a>
                        </li>
                        @permission('create-customer-complaints')
                            <li>
                                <a class="dropdown-item" href="{{ route('biller.customer_complains.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Customer Complaint</a>
                            </li>
                        @endauth
                    </ul>
                </li>
                @endauth
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
        {{-- miscellaneous module --}}
{{--        @if (access()->allowMultiple(['manage-note', 'manage-event', 'manage-project', 'manage-invoice']))--}}
{{--            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link"--}}
{{--                                                                  href="#" data-toggle="dropdown"><i class="icon-star"></i><span>Library</span></a>--}}
{{--                <ul class="dropdown-menu">--}}
{{--                    @permission('manage-note')--}}
{{--                    <li><a class="dropdown-item" href="{{ route('biller.notes.index') ? '#' : '#' }}"--}}
{{--                           data-toggle="dropdown"><i class="icon-note"></i> {{ trans('general.notes') }}</a>--}}
{{--                    </li>--}}
{{--                    @endauth--}}
{{--                    @permission('manage-invoice')--}}
{{--                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a--}}
{{--                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i--}}
{{--                                    class="icon-umbrella"></i> Fault Management</a>--}}
{{--                        <ul class="dropdown-menu">--}}
{{--                            <li><a class="dropdown-item" href="{{ route('biller.faults.index') }}"--}}
{{--                                   data-toggle="dropdown"><i class="ft-file-text"></i> Manage Fault--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li><a class="dropdown-item" href="{{ route('biller.faults.create') }}"--}}
{{--                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Fault--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                    @endauth--}}
{{--                    @permission('manage-event')--}}
{{--                    <li><a class="dropdown-item" href="{{ route('biller.events.index') }}"--}}
{{--                           data-toggle="dropdown"><i class="icon-calendar"></i>--}}
{{--                            {{ trans('features.calendar') }}</a>--}}
{{--                    </li>--}}
{{--                    @endauth--}}

{{--                    @permission('manage-project')--}}
{{--                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a--}}
{{--                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i--}}
{{--                                    class="icon-tag"></i> IRD Jobcard</a>--}}
{{--                        <ul class="dropdown-menu">--}}
{{--                            <li><a class="dropdown-item" href="{{ '#' }}" data-toggle="dropdown"> <i--}}
{{--                                            class="fa fa-compass"></i> IRD Report</a></li>--}}
{{--                            <li><a class="dropdown-item" href="{{ '#' }}" data-toggle="dropdown"> <i--}}
{{--                                            class="fa fa-plus-circle"></i> Create IRD Report</a></li>--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                    @endauth--}}

{{--                    @permission('manage-invoice')--}}
{{--                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a--}}
{{--                                class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i--}}
{{--                                    class="icon-umbrella"></i> {{ trans('invoices.subscriptions') }}</a>--}}
{{--                        <ul class="dropdown-menu">--}}
{{--                            <li><a class="dropdown-item"--}}
{{--                                   href="{{ route('biller.invoices.index') ? '#' : '#' }}?md=sub"--}}
{{--                                   data-toggle="dropdown"><i class="ft-file-text"></i>--}}
{{--                                    {{ trans('invoices.subscriptions') }}--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li><a class="dropdown-item"--}}
{{--                                   href="{{ route('biller.invoices.create') ? '#' : '#' }}?sub=true"--}}
{{--                                   data-toggle="dropdown"><i class="fa fa-plus-circle"></i>--}}
{{--                                    {{ trans('invoices.create_subscription') }}--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                    @endauth--}}

                    <!-- Refill Service Management -->
                    {{-- @if (access()->allowMultiple(['manage-refill', 'manage-refill-product-category', 'manage-refill-product', 'manage-refill-customer']))
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-recycle" aria-hidden="true"></i> Refill Management</a>
                            <ul class="dropdown-menu">
                                @permission('manage-refill')
                                <li><a class="dropdown-item" href="{{ route('biller.product_refills.index') }}" data-toggle="dropdown"><i class="ft-file-text"></i> Manage Refills
                                    </a>
                                </li>
                                @endauth
                                @permission('create-refill')
                                <li><a class="dropdown-item" href="{{ route('biller.product_refills.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Refill
                                    </a>
                                </li>
                                @endauth
                                @permission('manage-refill-product-category')
                                <li><a class="dropdown-item" href="{{ route('biller.refill_product_categories.index') }}" data-toggle="dropdown"><i class="fa fa-object-ungroup"></i> Product Categories
                                    </a>
                                </li>
                                @endauth
                                @permission('manage-refill-product')
                                <li><a class="dropdown-item" href="{{ route('biller.refill_products.index') }}" data-toggle="dropdown"><i class="fa fa-cube"></i> Products
                                    </a>
                                </li>
                                @endauth
                                @permission('manage-refill-customer')
                                <li><a class="dropdown-item" href="{{ route('biller.refill_customers.index') }}" data-toggle="dropdown"><i class="ft-users"></i></i> Customers
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth --}}
{{--                </ul>--}}
{{--            </li>--}}
{{--        @endif--}}


        {{-- Client Area Module --}}
        @if (true)
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="fa fa-anchor"></i><span>PME Area</span></a>
                <ul class="dropdown-menu">
                    @if(auth()->user()->ins == 2 || auth()->user()->business->is_main == 1)
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-cubes" aria-hidden="true"></i> Subscription Packages</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.subscription-packages.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Subscription Packages</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.subscription-packages.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Subscription Package</a></li>
                            </ul>
                        </li>                        
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-check-square-o" aria-hidden="true"></i> Account Services</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_services.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Account Services</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_services.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Account Service</a></li>
                            </ul>
                        </li>

                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-university"></i> Business Accounts</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.tenants.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Business Accounts</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.tenants.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Business Account</a></li>
                            </ul>
                        </li>
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-gear"></i> SMS Settings</a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.send_sms.index_sms_settings') }}" data-toggle="dropdown"><i class="ft-list"></i>Set SMS</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-gear"></i> SMS Reports</a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.send_sms.index_send_sms') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Sms Report</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('biller.commissions.all_commission') }}"
                                data-toggle="dropdown"><i class="ft-list"></i> Manage All Commision
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('biller.sale_agents.index') }}"
                                data-toggle="dropdown"><i class="ft-list"></i> Manage Sale Agents
                            </a>
                        </li>
                    @endif

                    @permission('manage-client-area-ticket')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-comments-o" aria-hidden="true"></i> Support Tickets</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_tickets.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Support Tickets</a></li>
                                @permission('create-client-area-ticket')
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_tickets.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Ticket</a></li>
                                @endauth
                            </ul>
                        </li>
                    @endauth
                    
                    <li><a class="dropdown-item" href="{{ route('biller.tenants.subscription-details', auth()->user()->ins) }}" data-toggle="dropdown"><i class="ft-list"></i> Subscription Details </a></li>
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-usd" aria-hidden="true"></i> Invoices & Deposits</a>
                        <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_invoices.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Invoices</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_deposits.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Deposits</a></li>
                            {{--<li><a class="dropdown-item" href="{{ route('biller.mpesa_deposits.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage M-PESA Deposits</a></li>--}}
                        </ul>
                    </li>
                </ul>
            </li>
        @endif



        {{-- data & reports module --}}
        @permission('reports-statements')
        <li class="dropdown mega-dropdown nav-item" data-menu="megamenu"><a class="dropdown-toggle nav-link"
                                                                            href="#" data-toggle="dropdown"><i
                        class="icon-pie-chart"></i><span>{{ trans('features.reports') }}</span></a>
            <ul class="mega-dropdown-menu dropdown-menu row">
                {{-- statements --}}
                <li class="col-md-3" data-mega-col="col-md-3">
                    <ul class="drilldown-menu">
                        <li class="menu-list">
                            <ul class="mega-menu-sub">
                                <li class="nav-item text-bold-600 ml-1 text-info p-1">{{ trans('meta.statements') }}
                                </li>
                               <li>
                                   <a class="dropdown-item" href="#"><i
                                               class="fa fa-book"></i>{{ trans('meta.finance_account_statement') }}
                                   </a>
                                   <ul class="mega-menu-sub">
                                       <li><a class="dropdown-item"
                                              href="{{ route('biller.reports.statements', ['account']) }}"><i
                                                       class="icon-doc"></i>
                                               {{ trans('meta.finance_account_statement') }}
                                           </a>
                                       </li>
                                       <li><a class="dropdown-item"
                                              href="{{ route('biller.reports.statements', ['income']) }}"><i
                                                       class="icon-doc"></i> {{ trans('meta.income_statement') }}</a>
                                       </li>
                                       <li><a class="dropdown-item"
                                              href="{{ route('biller.reports.statements', ['expense']) }}"><i
                                                       class="icon-doc"></i> {{ trans('meta.expense_statement') }}</a>
                                       </li>
                                       <li><a class="dropdown-item"
                                              href="{{ route('biller.reports.statements', ['pos_statement']) }}"><i
                                                       class="icon-doc"></i> {{ trans('meta.pos_statement') }}</a>
                                       </li>
                                   </ul>
                               </li>


                               <li>
                                   <a class="dropdown-item" href="#"><i
                                               class="fa fa-smile-o"></i>{{ trans('customers.customer') }}</a>
                                   <ul class="mega-menu-sub">
                                       <li><a class="dropdown-item"
                                              href="{{ route('biller.reports.statements', ['customer']) }}"
                                              data-toggle="dropdown">{{ trans('meta.customer_statements') }}</a>
                                       </li>
                                       <li><a class="dropdown-item"
                                              href="{{ route('biller.reports.statements', ['product_customer_statement']) }}"
                                              data-toggle="dropdown">{{ trans('meta.product_customer_statement') }}</a>
                                       </li>
                                   </ul>
                               </li>


                               <li>
                                   <a class="dropdown-item" href="#"><i
                                               class="fa fa-truck"></i>{{ trans('suppliers.supplier') }}</a>
                                   <ul class="mega-menu-sub">
                                       <li><a class="dropdown-item"
                                              href="{{ route('biller.reports.statements', ['supplier']) }}"
                                              data-toggle="dropdown">{{ trans('meta.supplier_statements') }}</a>
                                       </li>
                                       <li><a class="dropdown-item"
                                              href="{{ route('biller.reports.statements', ['product_supplier_statement']) }}"
                                              data-toggle="dropdown">{{ trans('meta.product_supplier_statement') }}</a>
                                       </li>
                                   </ul>
                               </li>


                               <li>
                                   <a class="dropdown-item" href="#"><i
                                               class="icon-doc"></i>{{ trans('meta.tax_statements') }}</a>
                                   <ul class="mega-menu-sub">
                                       <li><a class="dropdown-item"
                                              href="{{ route('biller.reports.statements', ['tax']) }}"
                                              data-toggle="dropdown">{{ trans('meta.tax_statements') }}
                                               </a>
                                       </li>
                                       {{-- <li><a class="dropdown-item"
                                              href="{{ route('biller.reports.statements', ['tax']) }}?s=purchase"
                                              data-toggle="dropdown">{{ trans('meta.tax_statements') }}
                                               {{ trans('meta.purchase') }}</a>
                                       </li> --}}
                                   </ul>
                               </li>


                                <li><a class="dropdown-item" href="#"><i class="fa fa-th"></i>{{trans('meta.product_statement')}}</a>
                                    <ul class="mega-menu-sub">
                                        {{-- <li><a class="dropdown-item" href="{{route('biller.reports.statements',['product_statement'])}}" data-toggle="dropdown">{{trans('meta.product_statement')}}</a>
                                        </li> --}}
                                        <li><a class="dropdown-item" href="{{route('biller.reports.statements',['product_category_statement'])}}" data-toggle="dropdown">{{trans('meta.product_category_statement')}}</a></li>
                                        <li><a class="dropdown-item" href="{{route('biller.reports.statements',['product_warehouse_statement'])}}" data-toggle="dropdown">{{trans('meta.product_warehouse_statement')}}</a></li>
                                        <li><a class="dropdown-item" href="{{ route('biller.reports.statements',['product_movement_statement'])}}" data-toggle="dropdown">Products Movement Statement</a></li>
                                    </ul>
                                </li>
                                <li><a class="dropdown-item" href="#"><i
                                                class="fa fa-road"></i>{{ trans('products.stock_transfer') }}</a>
                                    <ul class="mega-menu-sub">
                                        <li><a class="dropdown-item"
                                               href="{{ route('biller.reports.statements', ['stock_transfer']) }}"
                                               data-toggle="dropdown">{{ trans('meta.stock_transfer_statement_warehouse') }}</a>
                                        </li>
                                        <li><a class="dropdown-item"
                                               href="{{ route('biller.reports.statements', ['stock_transfer_product']) }}"
                                               data-toggle="dropdown">{{ trans('meta.stock_transfer_statement_product') }}</a>
                                        </li>
                                        <li><a class="dropdown-item"
                                               href="{{ route('biller.project-sir.index') }}"
                                               data-toggle="dropdown"> Project Materials Report </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                {{-- grpahical reports --}}
                <li class="col-md-3" data-mega-col="col-md-3">
                    <ul class="drilldown-menu">
                        <li class="menu-list">
                            <ul class="mega-menu-sub">
                                <li class="nav-item text-bold-600 ml-1 text-info p-1">
                                    {{ trans('meta.graphical_reports') }}
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="{{ route('biller.reports.charts', ['customer']) }}"><i
                                                class="fa fa-bar-chart"></i>
                                        {{ trans('meta.customer_graphical_overview') }}
                                    </a>
                                </li>
                               <li data-menu=""><a class="dropdown-item"
                                                   href="{{ route('biller.reports.charts', ['supplier']) }}"><i
                                               class="fa fa-sun-o"></i> {{ trans('meta.supplier_graphical_overview') }}
                                   </a>
                               </li>
                               <li data-menu=""><a class="dropdown-item"
                                                   href="{{ route('biller.reports.charts', ['product']) }}"><i
                                               class="ft-trending-up"></i>
                                       {{ trans('meta.product_graphical_overview') }}
                                   </a>
                               </li>
                               <li data-menu=""><a class="dropdown-item"
                                                   href="{{ route('biller.reports.charts', ['income_vs_expenses']) }}"><i
                                               class="icon-pie-chart"></i>
                                       {{ trans('meta.income_vs_expenses_overview') }}
                                   </a>
                               </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                {{-- summary reports --}}
               <li class="col-md-3" data-mega-col="col-md-3">
                   <ul class="drilldown-menu">
                       <li class="menu-list">
                           <ul class="mega-menu-sub">
                               <li class="nav-item text-bold-600 ml-1 text-info p-1">
                                   {{ trans('meta.summary_reports') }}
                               </li>
                               {{-- <li data-menu=""><a class="dropdown-item"
                                                   href="{{ route('biller.reports.summary', ['income']) }}"><i
                                               class="ft-check-circle"></i> {{ trans('meta.income_summary') }}</a>
                               </li>
                               <li data-menu=""><a class="dropdown-item"
                                                   href="{{ route('biller.reports.summary', ['expense']) }}"><i
                                               class="fa fa fa-bullhorn"></i> {{ trans('meta.expense_summary') }}</a>
                               </li> --}}
                               <li data-menu=""><a class="dropdown-item"
                                                   href="{{ route('biller.reports.summary', ['sale']) }}"><i
                                               class="ft-aperture"></i> {{ trans('meta.sale_summary') }}</a>
                               </li>
                               <li data-menu=""><a class="dropdown-item"
                                                   href="{{ route('biller.reports.summary', ['purchase']) }}"><i
                                               class="ft-disc"></i> {{ trans('meta.purchase_summary') }}</a>
                               </li>
                               <li data-menu=""><a class="dropdown-item"
                                                   href="{{ route('biller.reports.summary', ['products']) }}"><i
                                               class="ft-layers"></i> {{ trans('meta.products_summary') }}</a>
                               </li>
                               <li data-menu=""><a class="dropdown-item"
                                                    href="{{ route('biller.projects.get-project-report') }}"><i
                                                class="ft-layers"></i> Project Report </a>
                               </li>
                               <li data-menu=""><a class="dropdown-item"
                                                    href="{{ route('biller.stock_issues.get_issuance_report') }}"><i
                                                class="ft-layers"></i> Stock Movement Report(Stock Issue) </a>
                               </li>
                               <li data-menu=""><a class="dropdown-item"
                                                    href="{{ route('biller.products.show_product_inventory') }}"><i
                                                class="ft-layers"></i> All Stock Movement Report </a>
                               </li>
                               <li data-menu=""><a class="dropdown-item"
                                                    href="{{ route('biller.dbm-set-options') }}"><i
                                                class="ft-layers"></i> Daily Operational Summary Report </a>
                               </li>
                               <li data-menu=""><a class="dropdown-item"
                                                    href="{{ route('biller.purchase-class-breviary') }}"><i
                                                class="ft-layers"></i> Non-Project Purchase Classes Report </a>
                               </li>
                           </ul>
                       </li>
                   </ul>
               </li>

                {{-- import data --}}
                <li class="col-md-3" data-mega-col="col-md-3">
                    <ul class="drilldown-menu">
                        <li class="menu-list">
                            <ul class="mega-menu-sub">

                                <li class="nav-item text-bold-600 ml-1 text-info p-1">{{ trans('import.import') }}
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('biller.import.general', ['prospect']) }}">
                                        <i class="fa fa-file-excel-o"></i> Prospects
                                    </a>
                                </li>
                               <li>
                                   <a class="dropdown-item"
                                      href="{{ route('biller.import.general', ['customer']) }}">
                                       <i class="fa fa-file-excel-o"></i> Customers
                                   </a>
                               </li>
                               <li>
                                   <a class="dropdown-item"
                                      href="{{ route('biller.import.general', ['supplier']) }}">
                                       <i class="fa fa-file-excel-o"></i> Suppliers
                                   </a>
                               </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('biller.import.general', ['products']) }}">
                                        <i class="fa fa-file-excel-o"></i> Products
                                    </a>
                                </li>
{{--                                <li>--}}
{{--                                    <a class="dropdown-item"--}}
{{--                                       href="{{ route('biller.import.general', ['accounts']) }}">--}}
{{--                                        <i class="fa fa-file-excel-o"></i> Accounts--}}
{{--                                    </a>--}}
{{--                                </li>--}}
{{--                                <li>--}}
{{--                                    <a class="dropdown-item"--}}
{{--                                       href="{{ route('biller.import.general', ['transactions']) }}">--}}
{{--                                        <i class="fa fa-file-excel-o"></i> Transactions--}}
{{--                                    </a>--}}
{{--                                </li>--}}
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('biller.import.general', ['equipments']) }}">
                                        <i class="fa fa-file-excel-o"></i> Equipments
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('biller.import.general', ['client_pricelist']) }}">
                                        <i class="fa fa-file-excel-o"></i> Client Pricelist
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('biller.import.general', ['supplier_pricelist']) }}">
                                        <i class="fa fa-file-excel-o"></i> Supplier Pricelist
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('biller.import.general', ['invoices']) }}">
                                        <i class="fa fa-file-excel-o"></i> Invoices
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('biller.import.general', ['invoice_payments']) }}">
                                        <i class="fa fa-file-excel-o"></i> Invoice Payments
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('biller.import.general', ['casuals']) }}">
                                        <i class="fa fa-file-excel-o"></i> Casual Labourers
                                    </a>
                                </li>
                                {{-- @permission('manage-boqs') --}}
                                    <li>
                                        <a class="dropdown-item"
                                        href="{{ route('biller.import.general', ['boqs']) }}">
                                            <i class="fa fa-file-excel-o"></i> Bill of Quantity
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                        href="{{ route('biller.import.general', ['material_take_off']) }}">
                                            <i class="fa fa-file-excel-o"></i> Quote/Material Take Off (MTO)
                                        </a>
                                    </li>
                                {{-- @endauth --}}
                                
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
        @endauth


        {{--        <li class="nav-item" ><a class="dropdown-toggle nav-link" href="{{route('biller.employee-daily-log.index')}}"><i class="icon-clock"></i><span>Daily Log</span></a> --}}

        {{--        </li> --}}




    </div>
</div>
