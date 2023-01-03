<div class="column is-narrow is-menu">
    <aside class="menu">
        <div>
            <h2 class="is-logo has-text-centered"><i style="font-size:25px;margin-left:-6px" class="fa fa-terminal"></i>cesma
            </h2>

            <div class="menu-items">
                <p class="menu-label">Aktion</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('perform-ssh') }}" class="execute {{ (request()->is('perform-ssh')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-terminal"></i></span> Befehl ausführen
                        </a>
                    </li>
                </ul>

                <p class="menu-label">Verwaltung</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ (request()->is('/')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-bars-progress"></i></span> Switch
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('vlans') }}" class="{{ (request()->is('vlans')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-ethernet"></i></span> VLAN
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('locations') }}" class="{{ (request()->is('locations')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-location-dot"></i></span> Standort
                        </a>
                    </li>
                </ul>

                <p class="menu-label">Daten</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('trunks') }}" class="{{ (request()->is('trunks')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-circle-nodes"></i></span> Trunk
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('backups') }}" class="{{ (request()->is('backups')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa-solid fa-hard-drive"></i></span> Backups
                        </a>
                    </li>
                </ul>

                <p class="menu-label">Benutzer</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('user-settings') }}" class="{{ (request()->is('user-settings')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-user-gear"></i></span> Einstellungen
                        </a>
                    </li>
                </ul>

                <p class="menu-label">System</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('user-management') }}" class="{{ (request()->is('user-management')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa fa-gear"></i></span> Benutzer
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('logs') }}" class="{{ (request()->is('logs')) ? 'has-text-primary' : '' }}">
                            <span class="icon"><i class="fa-solid fa-clock-rotate-left"></i></span> Log
                        </a>
                    </li>
                    <li>
                        <a class="" href="{{ route('logout') }}" onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
                            <span class="icon"><i class="fa-solid fa-power-off"></i></span> Abmelden
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>


                <p class="has-text-centered is-username-info" style="color:lightgrey;font-size:9pt;text-transform:uppercase">{{ Auth::user()->name }}<br>cesma v1.0.1</p>
            </div>

        </div>
    </aside>
</div>