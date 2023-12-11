<nav id="navbar-main" class="navbar is-fixed-top">
    <div class="navbar-brand">
        <a class="navbar-item is-hidden-desktop jb-aside-mobile-toggle">
            <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
        </a>
        <section class="section is-title-bar p-0 pl-4 m-0 is-flex">
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <ul>
                            @if(Route::currentRouteName() != "users" &&
                                Route::currentRouteName() != "logs" &&
                                Route::currentRouteName() != "publickeys" &&
                                Route::currentRouteName() != "snmp" &&
                                Route::currentRouteName() != "mac-types")
                           <li class="breadcrumb-item breadcrumb-color">
                                <a href="/">
                                    {{ Auth::user()->currentSite()->name }}
                                </a>
                            </li>
                            @endif
                            <x-breadcrumbs />
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="navbar-brand is-right">
        <a class="navbar-item is-hidden-desktop jb-navbar-menu-toggle" data-target="navbar-menu">
            <span class="icon"><i class="mdi mdi-dots-vertical"></i></span>
        </a>
    </div>
    <div class="navbar-menu fadeIn animated faster" id="navbar-menu">
        <div class="navbar-end">
            <div class="navbar-item has-dropdown has-dropdown-with-icons has-divider is-hoverable">
                <a class="navbar-link is-arrowless">
                    <div class="is-user-name"><span><i class="mdi mdi-web"></i>
                            {{ Auth::user()->currentSite()->name }}</span></div>
                    <span class="icon"><i class="mdi mdi-chevron-down"></i></span>
                </a>
                @if (count(Auth::user()->availableSites()) > 1)
                    <div class="navbar-dropdown">
                        <form id="change-site" action="{{ route('change-site') }}" method="post">
                            @method('PUT')
                            @csrf
                            <input type="hidden" value="" name="site_id" class="change-site-input">
                            @foreach (Auth::user()->availableSites() as $site)
                                @if ($site->id == Auth::user()->currentSite()->id)
                                    @continue
                                @endif

                                <a data-id="{{ $site->id }}" class="change-site-link navbar-item">
                                    <span>{{ $site->name }}</span>
                                </a>
                            @endforeach
                            </select>
                        </form>
                    </div>
                @endif
            </div>
            <div class="navbar-item has-dropdown has-dropdown-with-icons has-divider has-user-avatar is-hoverable">
                <a class="navbar-link is-arrowless">
                    <div class="is-user-avatar">
                        <img src="https://api.dicebear.com/7.x/initials/svg?seed={{ Auth::user()->initials() }}.svg"
                            alt="{{ Auth::user()->name }}">
                    </div>
                    <div class="is-user-name"><span>{{ Auth::user()->name }}</span></div>
                    <span class="icon"><i class="mdi mdi-chevron-down"></i></span>
                </a>
                <div class="navbar-dropdown">
                    {{-- <a class="navbar-item">
                        <span class="icon"><i class="mdi mdi-cog"></i></span>
                        <span>Settings</span>
                    </a> --}}
                    {{-- <hr class="navbar-divider"> --}}
                    <form id="logoutForm" method="POST" action="/logout">
                        @csrf
                        <a class="navbar-item" onclick='document.getElementById("logoutForm").submit()'>
                            <span class="icon"><i class="mdi mdi-logout"></i></span>
                            <span>{{ __('Log Out') }}</span>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<aside style="overflow:auto" class="aside is-placed-left is-expanded">
    <div class="aside-tools has-text-centered p-0">
        <div style="width:100%" class="aside-tools-label has-text-centered">
            <span class="has-kdam-pro-text is-size-3"><span class="mdi mdi-console-line"></span> cesma</span>
        </div>
    </div>
    <div class="menu is-menu-main">
        <p class="menu-label">{{ __('General') }}</p>
        <ul class="menu-list">
            <li>
                <a href="{{ route('dashboard') }}" class="@if (Route::currentRouteName() == 'dashboard') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-monitor"></i></span>
                    <span class="menu-item-label">{{ __('Dashboard') }}</span>
                </a>
            </li>
        </ul>
        <p class="menu-label">{{ __('Action') }}</p>
        <ul class="menu-list">
            <li>
                <div style="position: absolute;width:100%;height:42.5px;line-height:42.5px;text-align:center;color:white;background:rgba(0,0,0,.5)">
                    Not available
                </div>
                <a href="{{ route('ssh') }}" class="@if (Route::currentRouteName() == 'ssh') is-active @endif has-icon">
                    {{-- has-update-mark --}}
                    <span class="icon"><i class="mdi mdi-ssh"></i></span>
                    <span class="menu-item-label">{{ __('Execute SSH') }}</span>
                </a>
            </li>
        </ul>

        <p class="menu-label">{{ __('Data') }}</p>
        <ul class="menu-list">
            <li>
                <a href="{{ route('devices') }}" class="@if (Route::currentRouteName() == 'devices') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-switch"></i></span>
                    <span class="menu-item-label">Switches</span>
                </a>
            </li>
            <li>
                <a href="{{ route('vlans') }}" class="@if (Route::currentRouteName() == 'vlans') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-network"></i></span>
                    <span class="menu-item-label">Vlans</span>
                </a>
            </li>
            <li>
                <a href="{{ route('clients') }}" class="@if (Route::currentRouteName() == 'clients') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-desktop-classic"></i></span>
                    <span class="menu-item-label">{{ __('Clients') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('topology') }}" class="@if (Route::currentRouteName() == 'topology') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-sitemap"></i></span>
                    <span class="menu-item-label">{{ __('Topology') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('backups') }}" class="@if (Route::currentRouteName() == 'backups') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-backup-restore"></i></span>
                    <span class="menu-item-label">Backups</span>
                </a>
            </li>
        </ul>

        <p class="menu-label">{{ __('Site management') }}</p>
        <ul class="menu-list">
            <li>
                <a href="{{ route('buildings') }}"
                    class="@if (Route::currentRouteName() == 'buildings') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-office-building"></i></span>
                    <span class="menu-item-label">{{ __('Buildings') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('rooms') }}" class="@if (Route::currentRouteName() == 'rooms') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-door"></i></span>
                    <span class="menu-item-label">{{ __('Rooms') }}</span>
                </a>
            </li>
        </ul>

        <p class="menu-label">{{ __('Management') }}</p>
        <ul class="menu-list">
            <li>
                <a href="{{ route('logs') }}" class="@if (Route::currentRouteName() == 'logs') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-math-log"></i></span>
                    <span class="menu-item-label">Logs</span>
                </a>
            </li>
            <li>
                <a href="{{ route('reports') }}" class="@if (Route::currentRouteName() == 'reports') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-file-chart-outline"></i></span>
                    <span class="menu-item-label">Reports</span>
                </a>
            </li>
            {{-- <li>
                <a href="{{ route('settings') }}"
                    class="@if (Route::currentRouteName() == 'settings') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-cog"></i></span>
                    <span class="menu-item-label">{{ __('Settings') }}</span>
                </a>
            </li> --}}
            <li>
                <a href="{{ route('sites') }}" class="@if (Route::currentRouteName() == 'sites') is-active @endif has-icon">
                    <span class="icon"><i class="mdi mdi-web"></i></span>
                    <span class="menu-item-label">{{ __('Sites') }}</span>
                </a>
            </li>
            <li class="menu-is-dropdown">
                <a class="@if (Route::currentRouteName() == 'settings') is-active @endif has-icon has-dropdown-icon">
                    <span class="icon"><i class="mdi mdi-view-list"></i></span>
                    <span class="menu-item-label">{{ __('Global settings') }}</span>
                    <div class="dropdown-icon">
                        <span class="icon"><i class="mdi mdi-plus"></i></span>
                    </div>
                </a>
                <ul>
                    <li>
                        <a href="{{ route('users') }}">
                            <span class="icon"><i class="mdi mdi-account-supervisor"></i></span>
                            <span>{{ __('Users') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('publickeys') }}">
                            <span class="icon"><i class="mdi mdi-key"></i></span>
                            <span>SSH Publickeys</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('mac-types') }}">
                            <span class="icon"><i class="mdi mdi-ethernet"></i></span>
                            <span>{{ __('MAC Types') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('snmp') }}">
                            <span class="icon"><i class="mdi mdi-ip"></i></span>
                            <span>SNMP</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</aside>
