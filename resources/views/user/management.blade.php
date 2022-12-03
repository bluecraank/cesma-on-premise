<x-layouts.main>
    @livewire('search-user')

    <div class="modal modal-new-user">
        <form action="/user/create" method="post">
            @csrf
            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">User erstellen</p>
                </header>
                <section class="modal-card-body">
                    <div class="field">
                        <label class="label">Name:</label>
                        <div class="control">
                            <input required class="input" name="name" type="text" placeholder="Name">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">E-Mail:</label>
                        <div class="control">
                            <input required class="input" name="email" type="email" placeholder="E-Mail">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Passwort:</label>
                        <div class="control">
                            <input required class="input" name="password" type="password" placeholder="New password">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Passwort wdh:</label>
                        <div class="control">
                            <input required class="input" name="password_confirmation" type="password" placeholder="Confirm password">
                        </div>
                    </div>

                </section>
                <footer class="modal-card-foot">
                    <button class="button is-success">Erstellen</button>
                    <button onclick="$('.modal-new-user').hide();return false;" type="button" class="button">Abbrechen</button>
                </footer>
            </div>
        </form>
    </div>
    <div class="modal modal-delete-user">
        <form action="/user/delete" method="post">
            @csrf
            <input type="hidden" value="DELETE" name="_method">
            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">User löschen</p>
                </header>
                <section class="modal-card-body">
                    <div class="field">
                        <input type="hidden" name="id" class="user-id" value="">
                        <label class="label">Möchtest du diesen User wirklich löschen?</label>
                        <p class="control has-icons-left">
                            <input class="input user-name" type="text" name="name" readonly="true">
                            <span class="icon is-small is-left">
                                <i class="fa fa-a"></i>
                            </span>
                        </p>
                    </div>
                </section>
                <footer class="modal-card-foot">
                    <button class="button is-success">Löschen</button>
                    <button onclick="$('.modal-delete-user').hide();return false;" type="button" class="button">Abbrechen</button>
                </footer>
            </div>
        </form>
    </div>
    </x-layouts>