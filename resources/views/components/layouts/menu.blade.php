<div class="column is-narrow is-menu">
    <aside class="menu">
        <div style="padding-bottom:40px;">
            <div class="has-text-centered">
                <i style="font-size:25px;margin-left:-6px" class="fa fa-terminal"></i>
                <span class="logo-text is-logo">cesma</span>
                <span style="margin-top:-10px;" class="is-block is-size-7 has-text-weight-bold">DEV</span>
            </div>

            <div class="menu-items">
                <p class="menu-label">{{ __('Change site') }}</p>
                
                <div class="site-selection">
                    <form id="site-form" action="{{ route('change-site') }}" method="post">

                        @method('PUT')
                        @csrf
                        <select onchange="event.preventDefault();document.getElementById('site-form').submit();"
                            name="site_id">
                            <option value="{{ Auth::user()->currentSite()->id }}">
                                {{ Auth::user()->currentSite()->name }}</option>
                                @foreach (Auth::user()->availableSites() as $site)
                                @if ($site->id == Auth::user()->currentSite()->id)
                                @continue
                                @endif
                                
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    
                @if(Auth::user()->role == 2)
                <p class="menu-label">{{ __('Action') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('perform-ssh') }}"
                            class="execute {{ request()->is('execute') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-terminal"></i></span>
                            <span>{{ __('Menu.Execute') }}</span>
                        </a>
                    </li>
                </ul>
                @endif
                <p class="menu-label">{{ __('Management') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ request()->is('/') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-bars-progress"></i></span>
                            <span>{{ __('Menu.Switches') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('backups') }}"
                            class="{{ request()->is('switch/backups') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fas fa-hard-drive"></i></span>
                            <span>{{ __('Menu.Backups') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('vlans') }}" class="{{ request()->is('vlans') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-ethernet"></i></span>
                            <span>{{ __('Menu.Vlans') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('clients') }}"
                            class="{{ request()->is('clients') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fas fa-computer"></i></span>
                            <span>{{ __('Menu.Clients') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('topology') }}"
                            class="{{ request()->is('topology') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa-solid fa-circle-nodes"></i></span>
                            <span>{{ __('Menu.Topology') }}</span>
                        </a>
                    </li>
                </ul>

                <p class="menu-label">{{ __('Site') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('sites') }}" class="{{ request()->is('sites') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-location-dot"></i></span>
                            <span>{{ __('Menu.Site') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('buildings') }}"
                            class="{{ request()->is('buildings') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa-solid fa-building"></i></span>
                            <span>{{ __('Buildings') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('rooms') }}" class="{{ request()->is('rooms') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa-solid fa-door-open"></i></span>
                            <span>{{ __('Rooms') }}</span>
                        </a>
                    </li>
                </ul>
                
                @if(Auth::user()->role == 2)
                <p class="menu-label">{{ __('System') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('system') }}"
                            class="{{ request()->is('system') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-gear"></i></span> <span>{{ __('Menu.System') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('logs') }}" class="{{ request()->is('logs') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fas fa-clock-rotate-left"></i></span>
                            <span>{{ __('Menu.Log') }}</span>
                        </a>
                    </li>
                </ul>
                @endif

                <p class="menu-label">{{ __('User') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('user-settings') }}"
                            class="{{ request()->is('user-settings') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-user-gear"></i></span>
                            <span>{{ __('Menu.Usersettings') }}</span>
                        </a>
                    </li>
                    <li>
                        <a id="actionLogout" href="#">
                            <span class="icon"><i class="fas fa-power-off"></i></span>
                            <span>{{ __('Menu.Logout') }}</span>
                        </a>
                        <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>

                <p class="has-text-centered is-username-info dark-fix-color is-static">
                    {{ Auth::user()->name }}<br>
                    CESMA {{ config('app.version') }}
                </p>

            </div>

        </div>
    </aside>
</div>
