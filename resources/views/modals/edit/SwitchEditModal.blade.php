<div class="modal modal-edit-switch">
    <form action="{{  route('update-switch') }}" method="post">
        @csrf
        @method('PUT')

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Switch.Edit.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('Switch.Name') }}</label>
                    <div class="control">
                        <input type="hidden" class="switch-id" name="id" value="">
                        <input class="input switch-name" name="name" type="text" value=""
                            placeholder="Name">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Switch.IP') }}</label>
                    <div class="control">
                        <input class="input switch-fqdn" name="hostname" type="text" value=""
                            placeholder="Hostname oder IP">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Switch.Password') }}</label>
                    <div class="control">
                        <input class="input switch-password" name="password" type="password" value="__hidden__"
                            placeholder="WebGUI Password">
                    </div>
                </div>
                <div class="columns">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label is-small">{{ __('Location') }}</label>
                            <div class="select is-fullwidth is-small">
                                <select class="switch-location" name="site_id">
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label is-small">Gebäude</label>
                            <div class="select is-fullwidth is-small">
                                <select class="switch-building" name="building_id">
                                    @foreach ($buildings as $building)
                                        <option value="{{ $building->id }}">{{ $building->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label is-small">Raum</label>
                            <div class="select is-fullwidth is-small">
                                <select class="switch-room" name="room_id">
                                    @foreach ($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field is-fullwidth">
                            <label class="label is-small">Reihenfolge</label>
                            <input class="input is-small is-fullwidth switch-numbering" name="location_description" type="number"
                                placeholder="1">
                        </div>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">{{ __('Button.Save') }}</button>
                <button data-modal="edit-switch" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
