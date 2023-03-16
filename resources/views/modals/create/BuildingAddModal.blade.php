<div class="modal modal-add-building">
    <form onsubmit="$('.modal-card-foot .submit').addClass('is-loading')" action="/building" method="post">

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Gebäude erstellen</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field is-fullwidth">
                    <label class="label">Standort wählen</label>
                    <p class="control">
                        <span class="select is-fullwidth">
                            <select required name="location_id">
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </span>
                    </p>
                </div>

                <label class="label">Gebäudename</label>
                <div class="field">
                    <p class="control is-expanded">
                        <input class="input" required type="text" name="name" placeholder="Gebäudename / Straße">
                    </p>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-add-building').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
