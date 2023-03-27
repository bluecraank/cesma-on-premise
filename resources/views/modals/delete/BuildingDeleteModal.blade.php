<div class="modal modal-delete-building">
    <form onsubmit="$('.submit').addClass('is-loading')" action="/building" method="post">
        @csrf
        @method('DELETE')
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Delete.Building') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('Delete.Building.Desc') }}</label>
                    <div class="control">
                        <input class="building-id" name="id" type="hidden" value="">
                        <input class="building-name" name="name" type="hidden" value="">
                        <input class="input building-name" disabled type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Delete') }}
                </button>
                <button onclick="$('.modal-delete-building').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>