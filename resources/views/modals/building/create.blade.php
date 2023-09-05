<div class="modal modal-create-building">
    <form action="{{ route('buildings') }}" method="post">

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Create building') }}</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field is-fullwidth">
                    <label class="label">{{ __('Select site') }}</label>
                    <p class="control">
                        <span class="select is-fullwidth">
                            <select name="site_id" readonly>
                                <option value="{{ Auth::user()->currentSite()->id }}">{{ Auth::user()->currentSite()->name }}</option>
                            </select>
                        </span>
                    </p>
                </div>

                <label class="label">Name</label>
                <div class="field">
                    <p class="control is-expanded">
                        <input class="input" required type="text" name="name" placeholder="Gebäudename / Straße">
                    </p>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Save') }}</button>
                <button data-modal="create-building" type="button"
                    class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
