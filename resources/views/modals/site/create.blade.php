<div class="modal modal-create-site">
    <form action="{{  route('create-site') }}" method="post">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Create site') }}</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field">
                    <label for="" class="label">Name</label>
                    <div class="control is-expanded">
                        <input required class="input" name="name" type="text" placeholder="AB XYZ">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Save') }}</button>
                <button data-modal="create-site" type="button"
                    class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
