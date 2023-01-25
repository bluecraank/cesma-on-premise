<div class="modal modal-new-switch">
    <form onsubmit="$(this).find('.button.is-success').addClass('is-loading');" action="/switch/create" method="post">
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
                        <input class="input" name="hostname" type="text" placeholder="Hostname / IP">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Switch.Password') }}</label>
                    <div class="control">
                        <input required class="input" name="password" type="password"
                            placeholder="{{ __('Switch.Password') }}">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Firmware</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select required name="type">
                                <option value="aruba-os">ArubaOS</option>
                                <option value="aruba-cx">ArubaCX</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">{{ __('Location') }}</label>
                    <div class="control">
                        <div class="select">
                            <select required name="location">
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="select">
                            <select required name="building">
                                @foreach ($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input class="input" required name="details" style="display: inline-block;width:160px" type="text"
                            placeholder="Department / Floor">
                        <input class="input" required name="number" style="display: inline-block;width:80px" type="number"
                            placeholder="1">
                    </div>
                </div>
            </section>

            <footer class="modal-card-foot">
                <button class="button is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-new-switch').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
