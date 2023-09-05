<div>
    <div class="modal modal-update-vlan" @if ($show && $modal == 'update') style="display:block" @endif>
        <form wire:submit="update">
            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Edit vlan') }}</p>
                </header>
                <section class="modal-card-body">
                    @if (!$show)
                        <div class="loader-wrapper is-active">
                            <div class="loader is-loading"></div>
                        </div>
                    @else
                        <div class="field">
                            <label class="label">Name</label>
                            <p class="control">
                                <input wire:model="name" class="input name @error('name') is-danger @enderror"
                                    name="name" placeholder="Name des VLANs">
                            </p>
                            @error('name')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label class="label">{{ __('Description') }}</label>
                            <p class="control">
                                <input wire:model="description"
                                    class="input description @error('description') is-danger @enderror"
                                    name="description" placeholder="{{ __('Description') }}">
                            </p>
                            @error('description')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label class="label">{{ __('IP-Network') }}</label>
                            <p class="control">
                                <input wire:model="ip_range"
                                    class="input ip_range @error('ip_range') is-danger @enderror" name="ip_range"
                                    placeholder="IP-Network">
                            </p>
                            @error('ip_range')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label class="checkbox @error('is_synced') is-danger @enderror">
                                <input wire:model="is_synced" type="checkbox" name="sync" class="is_synced"
                                    @if ($is_synced) checked @endif>
                                {{ __('Is syncable') }}
                            </label>
                            @error('is_synced')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label class="checkbox">
                                <input wire:model="is_client_vlan" type="checkbox" name="is_client_vlan"
                                    class="is_client_vlan @error('is_synced') is-danger @enderror"
                                    @if (!$is_client_vlan) checked @endif>
                                {{ __('Has no clients') }}
                            </label>
                            @error('is_client_vlan')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </section>
                <footer class="modal-card-foot">
                    <button class="button no-prevent is-success">{{ __('Save') }}</button>
                    <button wire:click="close()" type="button" class="button">{{ __('Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>
    <div class="modal modal-delete-vlan" @if ($show && $modal == 'delete') style="display:block" @endif>
        <form wire:submit="delete">
            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Delete vlan') }}</p>
                </header>
                <section class="modal-card-body">
                    @if (!$show)
                        <div class="loader-wrapper is-active">
                            <div class="loader is-loading"></div>
                        </div>
                    @else
                        <div class="field">
                            <label class="label">{{ __('Are you sure to delete this vlan?') }}</label>
                            <p class="control">
                                <input wire:model="name" class="input name" type="text" name="name"
                                    readonly="true">
                            </p>
                        </div>
                    @endif
                </section>
                <footer class="modal-card-foot">
                    <button class="button no-prevent is-danger">{{ __('Delete') }}</button>
                    <button wire:click="close()" type="button" class="button">{{ __('Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>
</div>
