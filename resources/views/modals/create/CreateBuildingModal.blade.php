<div class="modal modal-add-building">
    <form action="{{ route('buildings') }}" method="post">

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
                            <select name="site_id" readonly>
                                <option value="{{ Auth::user()->currentSite()->id }}">{{ Auth::user()->currentSite()->name }}</option>
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
                <button data-modal="add-building" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
