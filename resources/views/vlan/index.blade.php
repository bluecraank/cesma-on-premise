<x-layouts.main>
    @livewire('search-vlan')

    <div class="modal modal-edit-vlan">
        <form action="/vlan/update" method="post">
            @csrf
            @method('PUT')

            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">VLAN bearbeien</p>
                </header>
                <section class="modal-card-body">
                    <div class="field">
                        <input type="hidden" class="vlan-id" name="id">
                        <label class="label">VLAN Name</label>
                        <p class="control has-icons-left">
                            <input class="input vlan-name" name="name" placeholder="Name des VLANs">
                            <span class="icon is-small is-left">
                                <i class="fa fa-a"></i>
                            </span>
                        </p>
                    </div>
                    <div class="field">
                        <label class="label">VLAN Beschreibung (optional)</label>
                        <p class="control has-icons-left">
                            <input class="input vlan-desc" name="description" placeholder="VLAN Beschreibung">
                            <span class="icon is-small is-left">
                                <i class="fa fa-info"></i>
                            </span>
                        </p>
                    </div>
                    <div class="field">
                        <label class="label">IP-Bereich (optional)</label>
                        <p class="control has-icons-left">
                            <input class="input vlan-ip_range" name="ip_range" placeholder="10.10.10.0/24">
                            <span class="icon is-small is-left">
                                <i class="fa fa-up-down"></i>
                            </span>
                        </p>
                    </div>
                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="scan" class="vlan-scan">
                            NMAP-Scan aktivieren
                          </label>
                    </div>
                </section>
                <footer class="modal-card-foot">
                    <button class="button is-success">Speichern</button>
                    <button onclick="$('.modal-edit-vlan').hide();return false;" type="button" class="button">Abbrechen</button>
                </footer>
            </div>
        </form>
    </div>

    <div class="modal modal-delete-vlan">
        <form action="/vlan/delete" method="post">
            @csrf
            <input type="hidden" value="DELETE" name="_method">
            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">VLAN löschen</p>
                </header>
                <section class="modal-card-body">
                    <div class="field">
                        <input type="hidden" name="id" class="vlan-id" value="">
                        <label class="label">Möchtest du das VLAN wirklich löschen?</label>
                        <p class="control has-icons-left">
                            <input class="input vlan-name" type="text" name="name" readonly="true">
                            <span class="icon is-small is-left">
                                <i class="fa fa-a"></i>
                            </span>
                        </p>
                    </div>
                </section>
                <footer class="modal-card-foot">
                    <button class="button is-success">Löschen</button>
                    <button onclick="$('.modal-delete-vlan').hide();return false;" type="button" class="button">Abbrechen</button>
                </footer>
            </div>
        </form>
    </div>
    </x-layouts>