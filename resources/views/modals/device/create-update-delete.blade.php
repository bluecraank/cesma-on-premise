<div>
    <div class="modal modal-create-device" @if ($show && $modal == 'create') style="display:block" @endif>
        <form wire:submit="create">
            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Add switch') }}</p>
                </header>
                <section class="modal-card-body">
                    @if (!$show)
                        <div class="loader-wrapper is-active">
                            <div class="loader is-loading"></div>
                        </div>
                    @else
                        <div class="field">
                            <label class="label">Name</label>
                            <div class="control">
                                <input wire:model="name" required class="input @error('name') is-danger @enderror"
                                    name="name" type="text" placeholder="Name">
                            </div>
                            @error('name')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label class="label">{{ __('Hostname') }}</label>
                            <div class="control">
                                <input wire:model="hostname" class="input @error('hostname') is-danger @enderror"
                                    name="hostname" required type="text" placeholder="Hostname / IP">
                            </div>
                            @error('hostname')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="field">
                            <label class="label">{{ __('Password') }}</label>
                            <div class="control">
                                <input wire:model="password" required
                                    class="input @error('password') is-danger @enderror" required name="password"
                                    type="password" placeholder="{{ __('Password') }}">
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Firmware</label>
                            <div class="control">
                                <div class="select is-fullwidth  @error('type') is-danger @enderror">
                                    <select wire:model="type" required name="type">
                                        <option value="0">{{ __('Select device type') }}</option>
                                        @foreach (config('app.types') as $key => $type)
                                            <option value="{{ $key }}">{{ config('app.typenames')[$key] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('type')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label is-small">{{ __('Site') }}</label>
                                    <div class="select is-fullwidth is-small @error('site_id') is-danger @enderror">
                                        <select readonly="true" class="switch-location" name="site_id" required>
                                            <option value="{{ Auth::user()->currentSite()->id }}">
                                                {{ Auth::user()->currentSite()->name }}</option>
                                        </select>
                                    </div>
                                    @error('site_id')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label is-small">{{ __('Building') }}</label>
                                    <div class="select is-fullwidth is-small @error('building_id') is-danger @enderror">
                                        <select wire:change="updateBuildingId" wire:model="building_id"
                                            class="switch-building" name="building_id" required>
                                            <option value="0">{{ __('Select building') }}</option>
                                            @foreach ($buildings as $building)
                                                <option value="{{ $building->id }}">{{ $building->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('building_id')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label is-small">{{ __('Room') }}</label>
                                    <div class="select is-fullwidth is-small @error('room_id') is-danger @enderror">
                                        <select wire:model="room_id" class="switch-location" name="room_id" required>
                                            <option value="0">{{ __('Select room') }}</option>
                                            @foreach ($rooms as $room)
                                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('room_id')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field is-fullwidth">
                                    <label class="label is-small">{{ __('Description') }}</label>
                                    <input wire:model="description"
                                        class="input is-small is-fullwidth switch-numbering @error('description') is-danger @enderror"
                                        name="location_description" type="number" placeholder="1" value="1">
                                </div>
                                @error('description')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif
                </section>

                <footer class="modal-card-foot">
                    <button class="button submit no-prevent is-success">{{ __('Save') }}</button>
                    <button data-modal="create-device" type="button" class="button">{{ __('Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>

    <div class="modal modal-update-device" @if ($show && $modal == 'update') style="display:block" @endif>
        <form wire:submit="update">
            <div class="modal-background"></div>
            <div style="margin-top: 40px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Edit switch') }}</p>
                </header>
                <section class="modal-card-body">
                    @if (!$show)
                        <div class="loader-wrapper is-active">
                            <div class="loader is-loading"></div>
                        </div>
                    @else
                        <div class="field">
                            <label class="label">Name</label>
                            <div class="control">
                                <input wire:model="name" class="@error('name') is-danger @enderror input"
                                    name="name" type="text" value="" placeholder="Name">
                                @error('name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">{{ __('IP') }}</label>
                            <div class="control">
                                <input wire:model="hostname" class="@error('hostname') is-danger @enderror input"
                                    name="hostname" type="text" value="" placeholder="Hostname oder IP">
                                @error('hostname')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">{{ __('Password') }}</label>
                            <div class="control">
                                <input wire:model="password"
                                    class="@error('password') is-danger @enderror input switch-password"
                                    name="password" type="password" value="__hidden__" placeholder="Password">
                                @error('password')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label is-small">{{ __('Site') }}</label>
                                    <div class="@error('site_id') is-danger @enderror select is-fullwidth is-small">
                                        <select wire:model="site_id" class="site_id" name="site_id">
                                            @foreach ($sites as $site)
                                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('site_id')
                                            <p class="help is-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label is-small">{{ __('Building') }}</label>
                                    <div class="select is-fullwidth is-small">
                                        <select wire:model="building_id" wire:change="updateBuildingId"
                                            class="@error('building_id') is-danger @enderror" name="building_id">
                                            <option value="0">{{ __('Select building') }}</option>
                                            @foreach ($buildings as $building)
                                                <option value="{{ $building->id }}">{{ $building->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('building_id')
                                            <p class="help is-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label is-small">{{ __('Room') }}</label>
                                    <div class="@error('room_id') is-danger @enderror select is-fullwidth is-small">
                                        <select wire:model="room_id" class="room_id" name="room_id">
                                            <option value="0">{{ __('Select room') }}</option>
                                            @foreach ($rooms as $room)
                                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('room_id')
                                            <p class="help is-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field is-fullwidth">
                                    <label class="label is-small">{{ __('Description') }}</label>
                                    <input wire:model="description"
                                        class="@error('description') is-danger @enderror input is-small is-fullwidth location_description"
                                        name="location_description" type="number" placeholder="1">
                                    @error('description')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endif
                </section>
                <footer class="modal-card-foot">
                    <button type="submit" class="button no-prevent is-success">{{ __('Save') }}</button>
                    <button wire:click="close()" type="button" class="button">{{ __('Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>
    <div wire: class="modal modal-delete-device" @if ($show && $modal == 'delete') style="display:block" @endif>
        <form wire:submit="delete">
            <div class="modal-background"></div>
            <div style="margin-top: 40px" class="modal-card">
                <input type="hidden" name="_method" value="delete" />
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Delete switch') }}</p>
                </header>
                <section class="modal-card-body">
                    @if (!$show)
                        <div class="loader-wrapper is-active">
                            <div class="loader is-loading"></div>
                        </div>
                    @else
                        <div class="field">
                            <label class="label">{{ __('Are you sure to delete this switch?') }}</label>
                            <div class="control">
                                <input wire:model="name" class="input name" disabled type="text" value="">
                            </div>
                        </div>
                    @endif
                </section>
                <footer class="modal-card-foot">
                    <button type="submit" class="button no-prevent is-danger">{{ __('Delete') }}</button>
                    <button data-modal="delete-device" type="button" class="button">{{ __('Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>

</div>
