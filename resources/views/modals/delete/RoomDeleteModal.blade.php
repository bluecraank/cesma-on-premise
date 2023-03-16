<div class="modal modal-delete-room">
    <form onsubmit="$('.submit').addClass('is-loading')" action="/room" method="post">
        @csrf
        @method('DELETE')
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Delete.Room') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('Delete.Room.Desc') }}</label>
                    <div class="control">
                        <input class="room-id" name="id" type="hidden" value="">
                        <input class="room-name" name="name" type="hidden" value="">
                        <input class="input room-name" disabled type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Delete') }}
                </button>
                <button onclick="$('.modal-delete-room').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>