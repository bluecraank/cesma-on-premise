<x-layouts.main>
@livewire('search-switch')

<div class="modal modal-new-switch">
    <form action="/switch/create" method="post">
        @csrf
        <input type="hidden" value="AddSwitch" name="form">
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
                    <label class="label">Firmware</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select required name="switch-firmware">
                                <option selected value="">Bitte wählen...</option>
                                <option value="AOS">ArubaOS (z.B 2930F)</option>
                                <!-- <option value="AOS-CX">ArubaOS-CX (z.B 6100)</option> -->
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Standort</label>
                    <div class="control">
                        <div class="select">
                            <select required name="location">
                             <option value="1">Norden</option>
                            </select>
                        </div>
                        <div class="select">
                            <select required name="building">
                            <option value="1">Gebäude 1</option>
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
                <button name="submitT" type="submit" class="button is-success">Erstellen</button>
                <button onclick="$('.modal-new-switch').hide();return false;" type="button"
                    class="button">Abbrechen</button>
            </footer>
        </div>
    </form>
</div>
</x-layouts>
