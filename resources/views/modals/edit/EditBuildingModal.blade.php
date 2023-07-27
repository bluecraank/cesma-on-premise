<div class="modal modal-edit-building">
    <form action="{{ route('buildings') }}" method="post">
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
                        <input class="id" name="id" type="hidden" value="">
                        <input class="input name" name="name" type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-info">{{ __('Button.Save') }}</button>
                <button data-modal="edit-building" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
