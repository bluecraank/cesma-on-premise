<div class="modal modal-create-room">
    <form action="{{ route('rooms') }}" method="post">

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Create room') }}</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field is-fullwidth">
                    <label class="label">{{ __('Select building') }}</label>
                    <p class="control">
                        <span class="select is-fullwidth">
                            <select required name="building_id">
                                @foreach ($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </span>
                    </p>
                </div>

                <label class="label">Name</label>
                <div class="field">
                    <p class="control is-expanded">
                        <input required class="input" type="text" name="name" placeholder="{{ __('Building / Street') }}">
                    </p>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Save') }}</button>
                <button data-modal="create-room" type="button"
                    class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
