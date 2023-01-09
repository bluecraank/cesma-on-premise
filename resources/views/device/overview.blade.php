<x-layouts.main>
@livewire('search-switch')

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
</x-layouts>
