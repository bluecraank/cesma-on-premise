<x-layouts.main>
  <div class="box">
    <h1 class="title is-pulled-left">{{ __('Header.Usersettings') }}</h1>

    <div class="is-pulled-right ml-4">
    </div>

    <div class="is-pulled-right">
      <div class="field">
        <div class="control has-icons-right">

        </div>
      </div>
    </div>
    <form action="/user/update" method="post">
      @csrf
      @method('PUT')
      <div class="columns" style="margin-top: 70px;">

        <div class="column is-12">
          <div class="subtitle">{{ __('User.Settings.Password.Change') }}</div>
          <div class="field">
            <label class="label is-small">{{ __('Modal.User.Add.CurrentPassword') }}</label>
            <div class="control">
              <input class="input is-small" type="password" name="current_password" placeholder="Current password">
            </div>
          </div>

          <div class="field">
            <label class="label is-small">{{ __('Modal.User.Add.Password') }}</label>
            <div class="control">
              <input class="input is-small" type="password" name="new_password" placeholder="New password">
            </div>
          </div>

          <div class="field">
            <label class="label is-small">{{ __('Modal.User.Add.PasswordRepeat') }}</label>
            <div class="control">
              <input class="input is-small" type="password" name="new_password_confirm" placeholder="Confirm password">
            </div>
          </div>
        </div>

      </div>
      <div class="columns">
        <div class="column is-12">

          <div class="subtitle">{{ __('User.ChangeTheme') }}</div>
          <div class="field">
            <div class="control">
              <label class="label is-small">Themes</label>
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
      <button class="button is-small is-primary">{{ __('Button.Save') }}</button>
    </form>

    <div class="title mt-6">{{ __('User.Pubkey.Title') }}</div>
    <div class="columns">
      <div class="column is-12">
        <form action="/user/pubkey" method="post">
          @csrf
          @method('PUT')
          <div class="field">
            <label class="label is-small">{{ __('User.Pubkey.Content') }}</label>
            <div class="control">
              <textarea class="textarea" name="pubkey" placeholder="ssh-rsa ABFDHkJ2312..."></textarea>
            </div>
            <span class="help has-text-success"><i class="fa-solid fa-lock"></i> {{ __('User.Pubkey.Secure') }}</span>

          </div>
          Status:
          {!! ($pubkey) ? '<span class="has-text-success">'.__('User.Pubkey.Found').'</span>' : '<span class="has-text-warning">'.__('User.Pubkey.NotFound').'</span>' !!}
          <br><br>
          <button class="button is-small is-primary is-pulled-left">{{ __('Button.Save') }}</button>
        </form>

        <form action="/user/delete-pubkey" class="mt-1 is-pulled-right" method="POST">
          @csrf
          @method('DELETE')
          <button {{ ($pubkey) ? '' : 'disabled' }} @endphp class="button is-small is-danger">{{ __('User.Pubkey.Delete') }}</button>

        </form>
      </div>
    </div>
  </div>

  </x-layouts>