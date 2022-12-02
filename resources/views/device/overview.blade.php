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
                        <input required class="input" name="name" type="text" placeholder="Name des Switch">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Hostname / IP</label>
                    <div class="control">
                        <input class="input" name="hostname" type="text" placeholder="Hostname / IP des Switch">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Passwort</label>
                    <div class="control">
                        <input required class="input" name="password" type="password" placeholder="Passwort des Switch">
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
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">Erstellen</button>
                <button onclick="$('.modal-new-switch').hide();return false;" type="button"
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
