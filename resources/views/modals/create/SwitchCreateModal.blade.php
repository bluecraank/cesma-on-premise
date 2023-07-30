<div class="modal modal-new-switch">
    <form onsubmit="$(this).find('.button.is-success').addClass('is-loading');" action="{{  route('create-switch') }}" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Switch.Create.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('Switch.Name') }}</label>
                    <div class="control">
                        <input required class="input" name="name" type="text" placeholder="Name">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Switch.IP') }}</label>
                    <div class="control">
                        <input class="input" name="hostname" required type="text" placeholder="Hostname / IP">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Switch.Password') }}</label>
                    <div class="control">
                        <input required class="input" required name="password" type="password"
                            placeholder="{{ __('Switch.Password') }}">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Firmware</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select required name="type">
                                @foreach (config('app.types') as $key => $type)
                                    <option value="{{ $key }}">{{ config('app.typenames')[$key] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label is-small">{{ __('Location') }}</label>
                            <div class="select is-fullwidth is-small">
                                <select disabled class="switch-location" name="site_id" required>
                                    {{-- @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach --}}
                                    <option value="{{ Auth::user()->currentSite()->id }}">{{ Auth::user()->currentSite()->name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label is-small">Gebäude</label>
                            <div class="select is-fullwidth is-small">
                                <select class="switch-building" name="building_id" required>
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
                                <select class="switch-location" name="room_id" required>
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
                                placeholder="1" value="1" required>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="modal-card-foot">
                <button class="button submit no-prevent is-success">{{ __('Button.Save') }}</button>
                <button data-modal="new-switch" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
