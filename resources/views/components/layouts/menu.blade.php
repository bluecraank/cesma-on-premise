<div class="column is-narrow is-menu">
    <aside class="menu">
        <div>
            <h2 class="is-logo has-text-centered"><i style="font-size:25px" class="fa fa-terminal"></i>cesma
            </h2>

            <div class="menu-items">
                <p class="menu-label">Aktion</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('perform-ssh') }}" class="execute">
                            <span class="icon"><i class="fa fa-terminal"></i></span> Befehl ausführen
                        </a>
                    </li>
                </ul>

                <p class="menu-label">Verwaltung</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('dashboard') }}" class="switch">
                            <span class="icon"><i class="fa fa-bars-progress"></i></span> Switch
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('vlans') }}" class="vlan">
                            <span class="icon"><i class="fa fa-ethernet"></i></span> VLAN
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('trunks') }}" class="trunk">
                            <span class="icon"><i class="fa fa-circle-nodes"></i></span> Trunk
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('locations') }}" class="location">
                            <span class="icon"><i class="fa fa-location-dot"></i></span> Standort
                        </a>
                    </li>
                </ul>

                <p class="menu-label">Benutzer</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('user-settings') }}" class="settings">
                            <span class="icon"><i class="fa fa-user-gear"></i></span> Einstellungen
                        </a>
                    </li>
                </ul>

                <p class="menu-label">System</p>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('logs') }}" class="log">
                            <span class="icon"><i class="fa-solid fa-clock-rotate-left"></i></span> Log
                        </a>
                    </li>
                    <li>
                        <a class="" href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
                            <span class="icon"><i class="fa-solid fa-power-off"></i></span> Abmelden
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>


                <p class="has-text-centered menu-label is-username-info">cesma v1.0.0</p>
            </div>

        </div>
    </aside>
</div>