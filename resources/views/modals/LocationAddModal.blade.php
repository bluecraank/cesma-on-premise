<div class="modal modal-add-site">
    <div class="modal-background"></div>
    <div style="margin-top: 40px" class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Neuer Standort / Neues Gebäude</p>
        </header>
        <section class="modal-card-body">
            <form action="/location/create" method="post">
                @csrf
                <label class="label">Neuer Standort</label>
                <div class="field has-addons">
                    <div class="control is-expanded">
                        <input class="input" name="name" type="text" placeholder="Stadt / Ort">
                    </div>
                    <div class="control">
                        <button class="button is-primary">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
            </form>
            <br>
            <label class="label">Neues Gebäude</label>
            <form action="/building/create" method="post">
                @csrf
                <div class="field has-addons">
                    <p class="control">
                        <span class="select">
                            <select name="location_id">
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </span>
                    </p>
                    <p class="control is-expanded">
                        <input class="input" type="text" name="name" placeholder="Gebäudename / Straße">
                    </p>
                    <p class="control">
                        <button class="button is-primary">
                            <i class="fa fa-plus"></i>
                        </button>
                    </p>
            </form>
        </section>
        <footer class="modal-card-foot">
            <button onclick="$('.modal-add-site').hide();return false;" type="button" class="button">{{ __('Button.Cancel') }}</button>
        </footer>
    </div>
</div>
