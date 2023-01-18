<div class="modal modal-edit-uplinks">
    <form action="/switch/uplinks/update" method="post">
        @csrf
        @method('PUT')

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Uplink.Edit') }}</p>
            </header>
            <section class="modal-card-body">
                <input type="hidden" name="id" class="device-id input">

                <div class="field">
                    <label class="label">Switch</label>
                    <div class="control">
                        <input type="text" disabled name="name" class="device-name input"
                            placeholder="Switchname">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Uplinks</label>
                    <div class="control">
                        <input type="text" name="uplinks" class="device-uplinks input"
                            placeholder="Uplink1,Uplink2,50,51">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-edit-uplinks').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
