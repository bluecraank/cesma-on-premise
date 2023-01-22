<div class="modal modal-edit-switch">
    <form action="/switch/update" method="post">
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
                <div class="field">
                    <label class="label">{{ __('Location') }}</label>
                    <div class="control">
                        <div class="select">
                            <select class="switch-location" name="location">
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="select">
                            <select class="switch-building" name="building">
                                @foreach ($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input class="input switch-details" name="details" style="display: inline-block;width:200px"
                            type="text" placeholder="Department, Floor">
                        <input class="input switch-numbering" name="number" style="display: inline-block;width:40px"
                            type="text" placeholder="Number 1">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-edit-switch').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
