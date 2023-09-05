<div>
    <div class="modal modal-update-user" @if ($showModal && $type == 'update') style="display:block" @endif>
        <form wire:submit="save" method="post">
            <div class="modal-background"></div>
            <div style="margin-top: 40px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Update user permissions') }}</p>
                </header>
                <section class="modal-card-body">
                    @if (!$showModal)
                        <div class="loader-wrapper is-active">
                            <div class="loader is-loading"></div>
                        </div>
                    @else
                        <div class="field">
                            <label class="label">Name</label>
                            <div class="control">
                                <input wire:model="name" class="input" name="name" type="text" readonly="true"
                                    value="">
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">{{ __('Role') }}</label>
                            <div class="select is-fullwidth">
                                <select wire:model="role" class="role" name="role">
                                    <option @if ($role == 0) selected @endif value="0">
                                        {{ __('Read only user') }}</option>
                                    <option @if ($role == 1) selected @endif value="1">
                                        {{ __('Administrator') }}</option>
                                    <option @if ($role == 2) selected @endif value="2">
                                        {{ __('Super Administrator') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label m-0 p-0">{{ __('Sites') }}</label>
                            @foreach ($sites as $site)
                                <label class="checkbox mr-2 ml-2">
                                    <input @if ($role == 2) disabled checked @endif
                                        class="site-permission" type="checkbox" wire:model="permissions"
                                        value="{{ $site->id }}">
                                    {{ $site->name }}
                                </label>
                            @endforeach
                            @if ($role == 2)
                                <span
                                    class="help m-0 p-0 mb-3">{{ __('Super administrators have always full access to any site') }}</span>
                            @endif
                        </div>
                    @endif
                </section>
                <footer class="modal-card-foot">
                    <button class="button submit is-success">{{ __('Save') }}</button>
                    <button wire:click="close" data-modal="update-user" type="button" class="button">{{ __('Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>

    <div class="modal modal-delete-user" @if ($showModal && $type == 'delete') style="display:block" @endif>
        <form wire:submit="delete" method="post">
            <div class="modal-background"></div>
            <div style="margin-top: 40px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Delete user') }}</p>
                </header>
                <section class="modal-card-body">
                    @if (!$showModal)
                        <div class="loader-wrapper is-active">
                            <div class="loader is-loading"></div>
                        </div>
                    @else
                        <p>{{ __('Are you sure to delete this user?') }}</p>

                        <div class="field">
                            <label class="label">Name</label>
                            <div class="control">
                                <input wire:model="name" class="input" name="name" type="text" readonly="true"
                                    value="">
                            </div>
                        </div>
                    @endif
                </section>
                <footer class="modal-card-foot">
                    <button class="button submit is-danger">{{ __('Delete') }}</button>
                    <button wire:click="close" data-modal="delete-user" type="button" class="button is-info">{{ __('Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>
</div>
