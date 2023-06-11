<div class="modal modal-delete-location">
    <form onsubmit="$('.submit').addClass('is-loading')" action="{{  route('delete-site') }}" method="post">
        @csrf
        @method('DELETE')
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Delete.Location') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('Delete.Location.Desc') }}</label>
                    <div class="control">
                        <input class="location-id" name="id" type="hidden" value="">
                        <input class="location-name" name="name" type="hidden" value="">
                        <input class="input location-name" disabled type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Delete') }}
                </button>
                <button onclick="$('.modal-delete-location').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>