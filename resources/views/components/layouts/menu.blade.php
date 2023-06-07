<div class="column is-narrow is-menu">
    <aside class="menu">
        <div style="padding-bottom:40px;">
            <div class="has-text-centered">
                    <i style="font-size:25px;margin-left:-6px" class="fa fa-terminal"></i>
                    <span class="logo-text is-logo">cesma</span>
                    <span style="margin-top:-10px;" class="is-block is-size-7 has-text-weight-bold">PROD</span>
            </div>

            <div class="menu-items">
                <p class="menu-label">{{ __('Menu.Label.Action') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('perform-ssh') }}"
                            class="execute {{ request()->is('execute') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-terminal"></i></span>
                            <span>{{ __('Menu.Execute') }}</span>
                        </a>
                    </li>
                </ul>
                <p class="menu-label">{{ __('Menu.Label.Management') }}</p>
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
                        <a href="{{ route('vlans') }}"
                            class="{{ request()->is('vlans') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-ethernet"></i></span>
                            <span>{{ __('Menu.Vlans') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('uplinks') }}"
                            class="{{ request()->is('switch/uplinks') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-up-down"></i></span>
                            <span>{{ __('Menu.Uplinks') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('clients') }}"
                            class="{{ request()->is('clients') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fas fa-computer"></i></span>
                            <span>{{ __('Menu.Clients') }}</span>
                        </a>
                    </li>
                </ul>

                <p class="menu-label">{{ __('Menu.Label.User') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('user-settings') }}"
                            class="{{ request()->is('user-settings') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-user-gear"></i></span>
                            <span>{{ __('Menu.Usersettings') }}</span>
                        </a>
                    </li>
                </ul>

                <p class="menu-label">{{ __('Menu.Label.System') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('locations') }}"
                            class="{{ request()->is('locations') ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-location-dot"></i></span>
                            <span>{{ __('Menu.Locations') }}</span>
                        </a>
                    </li>
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
                    <li>
                        <a class="" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
                            <span class="icon"><i class="fas fa-power-off"></i></span>
                            <span>{{ __('Menu.Logout') }}</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>

                <p class="has-text-centered is-username-info dark-fix-color is-static">
                    {{ Auth::user()->name }}<br>
                    CESMA {{ config('app.version') }}
                </p>


                <button class="is-radiusless button is-fullwidth" style="position:absolute;bottom:0;"
                    onclick="collapseMenu(false, this)"><i class="fas fa-angle-left"></i></button>
            </div>

        </div>
    </aside>
</div>
