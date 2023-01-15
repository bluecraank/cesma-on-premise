<x-layouts.main>
@livewire('search-switch')
<div class="box">
    <div class="label is-small">Alle Switche</div>
    {{-- <div class="buttons are-small"> --}}
        {{-- <a class="button is-primary" href="/switch/uplinks"><i class="fa-solid fa-up-down mr-2"></i> Show Uplink-Ports</a> --}}
        {{-- <a class="button is-primary" href="/switch/trunks"><i class="fa-solid fa-circle-nodes mr-2"></i> Show Trunks</a> --}}

    {{-- </div> --}}
    <div class="buttons are-small">
        <form action="post" id="form-all-devices">
            @csrf
            <a onclick="device_overview_actions('backups', this)" class="button is-info"><i class="fa-solid fa-hdd mr-2"></i> Create Backup</a>
            <a onclick="$('.modal-sync-vlans').show();return false;" class="button is-info"><i class="fa-solid fa-ethernet mr-2"></i> Sync VLANs</a>
            <a onclick="device_overview_actions('pubkeys', this)" class="sync-pubkeys-button button is-info"><i class="fa-solid fa-sync mr-2"></i> Sync Pubkeys</a>
        </form>
    </div>
</div>

<div class="modal modal-new-switch">
    <form action="/switch/create" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Switch erstellen</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Bezeichner</label>
                    <div class="control">
                        <input required class="input" name="name" type="text" placeholder="Name">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Hostname / IP</label>
                    <div class="control">
                        <input class="input" name="hostname" type="text" placeholder="Hostname / IP">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Passwort für API</label>
                    <div class="control">
                        <input required class="input" name="password" type="password" placeholder="Passwort für API">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Firmware</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select required name="type">
                                <option value="aruba-os">ArubaOS</option>
                                <option value="aruba-cx">ArubaCX</option>
                            </select>
                        </div>
                    </div>
                </div>   

                <div class="field">
                    <label class="label">Standort</label>
                    <div class="control">
                        <div class="select">
                            <select required name="location">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="select">
                            <select required name="building">
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}">{{ $building->name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <input class="input" name="details" style="display: inline-block;width:200px"
                            type="text" placeholder="Standort">
                        <input class="input" name="number" style="display: inline-block;width:40px"
                            type="text" placeholder="1">
                    </div>
                </div>

                <div class="card">
                    <header class="card-header">
                      <p class="card-header-title">
                        Optionale Angaben
                      </p>
                      <a class="card-header-icon" aria-label="more options">
                        <span class="icon">
                          <i class="fas fa-angle-down" onclick="$('.msgoptionalopen').toggleClass('is-hidden')" aria-hidden="true"></i>
                        </span>
                    </a>
                    </header>
                    <div class="card-content msgoptionalopen is-hidden">
                        <div class="content">
                            <div class="field">
                                <label class="label">Uplink-Ports</label>
                                <div class="control">
                                    <input class="input" name="uplink_ports" type="text" placeholder="1,2,3,4">
                                </div>
                            </div>                          
                        </div>
                    </div>
                </div>
                    
            </section>

            <footer class="modal-card-foot">
                <button class="button is-success">Erstellen</button>
                <button onclick="$('.modal-new-switch').hide();return false;" type="button"
                    class="button">Abbrechen</button>
            </footer>
        </div>
    </form>
</div>

<div class="modal modal-edit-switch">
    <form action="/switch/update" method="post">
        @csrf
        @method('PUT')

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Switch bearbeiten</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Bezeichner</label>
                    <div class="control">
                        <input type="hidden" class="switch-id" name="id" value="">
                        <input class="input switch-name" name="name" type="text" value="" placeholder="Name">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Hostame oder IP</label>
                    <div class="control">
                        <input class="input switch-fqdn" name="hostname" type="text" value="" placeholder="Hostname oder IP">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Passwort für API</label>
                    <div class="control">
                        <input class="input switch-password" name="password" type="password" value="__hidden__"
                            placeholder="WebGUI Password">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Standort</label>
                    <div class="control">
                        <div class="select">
                            <select class="switch-location" name="location">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="select">
                            <select class="switch-building" name="building">
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}">{{ $building->name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <input class="input switch-details" name="details"
                            style="display: inline-block;width:200px" type="text" placeholder="z.B Abteilung">
                        <input class="input switch-numbering" name="number"
                            style="display: inline-block;width:40px" type="text" placeholder="1">
                    </div>
                </div>

                <div class="card">
                    <header class="card-header">
                      <p class="card-header-title">
                        Optionale Angaben
                      </p>
                      <a class="card-header-icon" aria-label="more options">
                        <span class="icon">
                          <i class="fas fa-angle-down" onclick="$('.msgoptionalopen').toggleClass('is-hidden')" aria-hidden="true"></i>
                        </span>
                    </a>
                    </header>
                    <div class="card-content msgoptionalopen is-hidden">
                        <div class="content">
                            <div class="field">
                                <label class="label">Uplink-Ports</label>
                                <div class="control">
                                    <input class="input switch-uplinks" name="uplinks" type="text" placeholder="1,2,3,4">
                                </div>
                            </div>                          
                        </div>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">Speichern</button>
                <button onclick="$('.modal-edit-switch').hide();return false;" type="button"
                    class="button">Abbrechen</button>
            </footer>
        </div>
    </form>
</div>

<div class="modal modal-delete-switch">
    <form action="/switch/delete" method="post">
        <input type="hidden" name="_method" value="delete" />
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Switch löschen</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Möchtest du wirklich diesen Switch löschen?</label>
                    <div class="control">
                        <input class="switch-id" name="id" type="hidden" value="">
                        <input class="switch-name" name="name" type="hidden" value="">
                        <input class="input switch-name" disabled type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-danger">Switch
                    löschen</button>
                <button onclick="$('.modal-delete-switch').hide();return false;" type="button"
                    class="button">Abbrechen</button>
            </footer>
        </div>
    </form>
</div>

<div class="modal modal-sync-pubkeys">
    <form action="/switch/every/pubkeys" onsubmit="event.preventDefault(); syncPubkeys();" id="form-sync-pubkeys" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Pubkeys synchronisieren</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Folgende Pubkeys werden synchronisiert:</label>
                    <div class="control">
                        <ul class="ml-5" style="list-style-type:circle">
                        @foreach ($keys as $key)
                        <li>{{ $key }}</li>
                        @endforeach
                        </ul>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-primary">Synchronisieren</button>
                <button onclick="$('.modal-sync-pubkeys').hide();return false;" type="button"
                    class="button">Abbrechen</button>
            </footer>
        </div>
    </form>
</div>

<div class="modal modal-sync-vlans">
    <form action="/switch/every/vlans" id="form-sync-vlans" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">VLANs synchronisieren</p>
            </header>
            <section class="modal-card-body">
                <div class="content">
                    <p>
                        Hiermit werden die in der Datenbank hinterlegten VLANs auf die Switche übertragen. (<a href="{{ route('vlans') }}">Zur VLAN-Übersicht</a>)<br>
                        <ul>
                            <li>VLANs werden nur erstellt, nicht gelöscht.</li>
                            <li>Namen werden überschrieben.</li>
                        </ul>
                        <br>
                    </p>

                    <label class="label">Optionen:</label>
                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="create-if-not-exists">
                            VLANs erstellen, wenn noch nicht vorhanden
                        </label>
                    </div>

                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="show-results" checked>
                            Ergebnisse anzeigen
                        </label>
                    </div>
                 </div> 
            </section>
            <footer class="modal-card-foot">
                <button class="button is-primary sync-vlan-start" onclick="$(this).addClass('is-loading');$('.sync-vlan-info').removeClass('is-hidden');$('.sync-vlan-cancel').addClass('is-hidden');">Synchronisieren</button>
                <button onclick="$('.modal-sync-vlans').hide();return false;" type="button"
                    class="button sync-vlan-cancel">Abbrechen</button>

                <span class="sync-vlan-info help is-size-6 is-hidden">Dies dauert einige Minuten! Bitte warten...</span>
            </footer>
        </div>
    </form>
</div>
</x-layouts>
