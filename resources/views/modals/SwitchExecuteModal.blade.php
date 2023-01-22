<div class="modal modal-confirmation-command">
    <div class="modal-background"></div>
    <div style="margin-top: 40px" class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">{{ __('Switch.SSH.Execute.Title') }}</p>
        </header>
        <section class="modal-card-body">
            <div class="field">
                <label class="label">{{ __('Switch.SSH.Execute.Desc') }}</label>
                <div class="control">
                    <textarea type="text" disabled class="textarea confirmation-content">
              </textarea>
                </div>
            </div>
        </section>
        <footer class="modal-card-foot">
            <button onclick="$('.modal-confirmation-command').hide();return false;" type="button"
                name="executeSwitchCommand" class="button is-danger">Ausführen</button>
            <button onclick="$('.modal-confirmation-command').hide();return false;" type="button"
                class="button">{{ __('Button.Cancel') }}</button>
        </footer>
    </div>
</div>
</div>
