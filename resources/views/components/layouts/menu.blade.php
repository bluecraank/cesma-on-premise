<div class="column is-narrow is-menu">
    <aside class="menu">
        <div style="padding-bottom:40px;">
            <h2 class="is-logo has-text-centered"><i style="font-size:25px;margin-left:-6px" class="fa fa-terminal"></i> cesma</h2>
            </h2>

            <div class="menu-items">
                <p class="menu-label">{{ __('Menu.Label.Action') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('perform-ssh') }}" class="execute {{ (request()->is('perform-ssh')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-terminal"></i></span> {{ __('Menu.Execute') }}
                        </a>
                    </li>
                </ul>
                <p class="menu-label">{{ __('Menu.Label.Management') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ (request()->is('/')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-bars-progress"></i></span> {{ __('Menu.Switches') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('backups') }}" class="{{ (request()->is('switch/backups')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fas fa-hard-drive"></i></span> {{ __('Menu.Backups') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('vlans') }}" class="{{ (request()->is('vlans')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-ethernet"></i></span> {{ __('Menu.Vlans') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('uplinks') }}" class="{{ (request()->is('switch/uplinks')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-up-down"></i></span> {{ __('Menu.Uplinks') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('clients') }}" class="{{ (request()->is('clients')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fas fa-computer"></i></span> {{ __('Menu.Clients') }}
                        </a>
                    </li>
                </ul>

                <p class="menu-label">{{ __('Menu.Label.Data') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('trunks') }}" class="{{ (request()->is('switch/trunks')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-circle-nodes"></i></span> {{ __('Menu.Trunks') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('locations') }}" class="{{ (request()->is('locations')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-location-dot"></i></span> {{ __('Menu.Locations') }}
                        </a>
                    </li>
                </ul>

                <p class="menu-label">{{ __('Menu.Label.User') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('user-settings') }}" class="{{ (request()->is('user-settings')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-user-gear"></i></span> {{ __('Menu.Usersettings') }}
                        </a>
                    </li>
                </ul>

                <p class="menu-label">{{ __('Menu.Label.System') }}</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('system') }}" class="{{ (request()->is('system')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-gear"></i></span> {{ __('Menu.System') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('logs') }}" class="{{ (request()->is('logs')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fas fa-clock-rotate-left"></i></span> {{ __('Menu.Log') }}
                        </a>
                    </li>
                    <li>
                        <a class="" href="{{ route('logout') }}" onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
                            <span class="icon"><i class="fas fa-power-off"></i></span> {{ __('Menu.Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>


                <p class="has-text-centered is-username-info" style="color:lightgrey;font-size:9pt;text-transform:uppercase">{{ Auth::user()->name }}<br>CESMA {{ config('app.version') }}</p>
            </div>

        </div>
    </aside>
</div>