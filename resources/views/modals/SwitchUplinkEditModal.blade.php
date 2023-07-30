<div class="modal modal-edit-uplinks">
    <form action="{{ route('uplinks') }}" method="post">
        @csrf
        @method('PUT')

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Uplink.Edit') }}</p>
            </header>
            <section class="modal-card-body">
                <input type="hidden" name="device_id" class="id input">

                <div class="field">
                    <label class="label">Switch</label>
                    <div class="control">
                        <input type="text" disabled name="name" class="name input"
                            placeholder="Switchname">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Uplinks</label>
                    <div class="control">
                        <input type="text" name="uplinks" class="uplinks input"
                            placeholder="49,50,51 or 49-51">
                    </div>

                    <span>Es gilt die Portnummer (1/1/2 => 2, 1/1/10:1 => 10)</span>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">{{ __('Button.Save') }}</button>
                <button data-modal="edit-uplinks" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
