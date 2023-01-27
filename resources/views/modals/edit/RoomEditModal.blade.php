<div class="modal modal-edit-room">
    <form onsubmit="$('.submit').addClass('is-loading')" action="/room" method="post">
        @csrf
        @method('PUT')
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Edit.Room') }}</p>
            </header>
            <section class="modal-card-body">

                <div class="field">
                    <label class="label">{{ __('Room') }}</label>
                    <div class="select is-fullwidth">
                        <select class="buildings" name="building_id" id="">
                            @foreach ($buildings as $building)
                                <option value="{{ $building->id }}">{{ $building->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="field">
                    <label class="label">{{ __('EditRoom') }}</label>
                    <div class="control">
                        <input class="room-id" name="id" type="hidden" value="">
                        <input class="input room-name" name="name" type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-edit-room').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
