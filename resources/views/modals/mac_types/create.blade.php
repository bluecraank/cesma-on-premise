<div class="modal modal-create-mac-type">
    <form action="{{ route('create-mac-type') }}" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Create mac type') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">MAC Prefix</label>
                    <p class="control">
                        <input class="input" name="mac_prefix" required placeholder="MAC Address oder Prefix">
                    </p>
                </div>
                <div class="field">
                    <label class="label">{{ __('Description') }}*</label>
                    <p class="control">
                        <input class="input" name="mac_desc" required placeholder="Type of devices">

                    </p>
                </div>

                <div class="field">
                    <label class="label">{{ __('Type') }}</label>
                    <p class="control">
                        <input class="input" name="mac_type_input" placeholder="MAC Type (e.g accesspoint)">
                    </p>
                </div>

                <label style="display:block" class="label has-text-centered">OR</label>

                <div class="field">
                    <div class="select is-fullwidth">
                        <select class="type" name="mac_type">
                            <option value="">{{ __('Select existing type') }}</option>
                            @foreach($mac_types as $type)
                                <option value="{{ $type->type }}">{{ $type->type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Save') }}</button>
                <button data-modal="create-mac-type" type="button"
                    class="button">{{ __('Cancel') }}</button>
            </footer>

        </div>
    </form>
</div>
