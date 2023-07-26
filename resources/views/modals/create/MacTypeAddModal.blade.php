<div class="modal modal-add-mac">
    <form action="/clients/type" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('MacFilter.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">MAC Prefix*</label>
                    <p class="control has-icons-left">
                        <input class="input" name="mac_prefix" required placeholder="MAC Address oder Prefix">
                        <span class="icon is-small is-left">
                            <i class="fa fa-a"></i>
                        </span>
                    </p>
                </div>
                <div class="field">
                    <label class="label">{{ __('Description') }}*</label>
                    <p class="control has-icons-left">
                        <input class="input" name="mac_desc" required placeholder="MAC Beschreibung">
                        <span class="icon is-small is-left">
                            <i class="fa fa-info"></i>
                        </span>
                    </p>
                </div>

                <div class="field">
                    <label class="label">{{ __('Typ') }}</label>
                    <p class="control has-icons-left">
                        <input class="input" name="mac_type_input" placeholder="MAC Typ (neu)">
                        <span class="icon is-small is-left">
                            <i class="fa fa-info"></i>
                        </span>
                    </p>
                </div>

                <label style="display:block" class="label has-text-centered">OR</label>

                <div class="field">
                    <div class="select is-fullwidth">
                        <select name="mac_type">
                            <option value="">{{ __('Misc.Select.SelectText') }}</option>
                            @foreach($mac_types as $type)
                                <option value="{{ $type->type }}">{{ $type->type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">{{ __('Button.Save') }}</button>
                <button data-modal="add-mac" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
