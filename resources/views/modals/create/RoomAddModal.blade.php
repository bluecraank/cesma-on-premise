<div class="modal modal-add-room">
    <form onsubmit="$('.modal-card-foot .submit').addClass('is-loading')" action="/room" method="post">

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Raum erstellen</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field is-fullwidth">
                    <label class="label">Gebäude wählen</label>
                    <p class="control">
                        <span class="select is-fullwidth">
                            <select name="building_id">
                                @foreach ($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </span>
                    </p>
                </div>

                <label class="label">Gebäudename</label>
                <div class="field">
                    <p class="control is-expanded">
                        <input class="input" type="text" name="name" placeholder="Gebäudename / Straße">
                    </p>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-add-room').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
