<div class="modal modal-edit-user">
    <form action="/user/role" method="post">
        <input class="guid" name="guid" type="hidden" value="">
        @csrf
        @method('PUT')
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Modal.User.Role') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Name</label>
                    <div class="control">
                        <input class="input name" name="name" type="text" readonly="true" value="">
                    </div>
                </div>

                <div class="field">
                    <label class="label">{{ __('Role') }}</label>
                    <div class="select is-fullwidth">
                        <select name="role">
                            <option {{ (Auth::user()->role == 0) ? 'selected' : '' }} class="user" value="0">{{ __('Role.User') }}</option>
                            <option {{ (Auth::user()->role == 1) ? 'selected' : '' }} class="admin" value="1">{{ __('Role.Admin') }}</option>
                            <option {{ (Auth::user()->role == 2) ? 'selected' : '' }} class="superadmin" value="2">{{ __('Role.SuperAdmin') }}</option>
                        </select>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-edit-user').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
