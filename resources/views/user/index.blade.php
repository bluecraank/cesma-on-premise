<x-layouts.main>
  <div class="box">
    <h1 class="title is-pulled-left">Meine Einstellungen</h1>

    <div class="is-pulled-right ml-4">
    </div>

    <div class="is-pulled-right">
      <div class="field">
        <div class="control has-icons-right">

        </div>
      </div>
    </div>
    <form action="/user/update" method="post" id="executeForm">
      @csrf
      @method('PUT')
      <div class="columns" style="margin-top: 70px;">

        <div class="column is-12">
          <div class="subtitle">Passwort ändern:</div>
          <div class="field">
            <label class="label is-small">Current password</label>
            <div class="control">
              <input class="input is-small" type="password" name="current_password" placeholder="Current password">
            </div>
          </div>

          <div class="field">
            <label class="label is-small">New password</label>
            <div class="control">
              <input class="input is-small" type="password" name="new_password" placeholder="New password">
            </div>
          </div>

          <div class="field">
            <label class="label is-small">Confirm new password</label>
            <div class="control">
              <input class="input is-small" type="password" name="new_password_confirm" placeholder="Confirm password">
            </div>
          </div>
        </div>

      </div>
      <div class="columns">
        <div class="column is-12">

          <div class="subtitle">Theme ändern:</div>
          <div class="field">
            <div class="control">
              <label class="label is-small">Theme auswählen</label>
              <div class="select is-small is-fullwidth">
                <select name="theme">
                  <option @php echo (isset($_COOKIE['theme']) and $_COOKIE['theme']=='light' ) ? 'selected' : '' @endphp value="light">Light</option>
                  <option @php echo (isset($_COOKIE['theme']) and $_COOKIE['theme']=='dark' ) ? 'selected' : '' @endphp value="dark">Dark</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
      <button class="button is-small is-primary">Speichern</button>
    </form>

    <div class="title mt-6">SSH: Öffentlicher Schlüssel</div>
    <div class="columns">
      <div class="column is-12">
        <form action="/user/pubkey" method="post">
          @csrf
          @method('PUT')
          <div class="field">
            <label class="label is-small">Inhalt SSH-Pubkey</label>
            <div class="control">
              <textarea class="textarea" name="pubkey" placeholder="ssh-rsa ABFDHkJ2312..."></textarea>
            </div>
            <span class="help has-text-success"><i class="fa-solid fa-lock"></i> Dein Öffentlicher Schlüssel wird verschlüsselt gespeichert</span>

          </div>
          Status:
          @php if($pubkey) { echo '<span class="has-text-success">Schlüssel vorhanden</span>'; $disabled = ""; } else { echo '<span class="has-text-warning">Schlüssel nicht vorhanden</span>'; $disabled = "disabled"; } @endphp
          <br><br>
          <button class="button is-small is-primary is-pulled-left">Hochladen</button>
        </form>

        <form action="/user/delete-pubkey" class="mt-1 is-pulled-right" method="POST">
          @csrf
          @method('DELETE')
          <button {{ $disabled }} @endphp class="button is-small is-danger">Schlüssel entfernen</button>

        </form>
      </div>
    </div>
  </div>

  </x-layouts>