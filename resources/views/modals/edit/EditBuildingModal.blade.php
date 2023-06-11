<div class="modal modal-edit-building">
    <form onsubmit="$('.submit').addClass('is-loading')" action="{{ route('buildings') }}" method="post">
        @csrf
        @method('PUT')
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Edit.Building') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Gebäudename</label>
                    <div class="control">
                        <input class="building-id" name="id" type="hidden" value="">
                        <input class="input building-name" name="name" type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-info">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-edit-building').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
