<x-layouts.main>

  @if ($errors->any())
  <div class="notification is-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  @if(session()->has('success'))
  <div class="notification is-success">
    {{ session()->get('success') }}
  </div>
  @endif

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

        <div class="column is-6">
          <div class="field">
            <label class="label is-small">Current password:</label>
            <div class="control">
              <input class="input is-small" type="password" name="current_password" placeholder="Current password">
            </div>
          </div>

          <div class="field">
            <label class="label is-small">New password:</label>
            <div class="control">
              <input class="input is-small" type="password" name="new_password" placeholder="New password">
            </div>
          </div>

          <div class="field">
            <label class="label is-small">New password again:</label>
            <div class="control">
              <input class="input is-small" type="password" name="new_password_confirm" placeholder="Confirm password">
            </div>
          </div>
        </div>

        <div class="column is-6">

          <div class="field">
            <label class="label is-small">Theme:</label>
            <div class="control">
              <div class="select is-small">
                <select name="theme">
                  <option @php echo (isset($_COOKIE['theme']) and $_COOKIE['theme']=='light' ) ? 'selected' : '' @endphp value="light">Light</option>
                  <option @php echo (isset($_COOKIE['theme']) and $_COOKIE['theme']=='dark' ) ? 'selected' : '' @endphp value="dark">Dark</option>
                </select>
              </div>
            </div>
          </div>


          <div class="field">
            <label class="label is-small">Privatekey:</label>
            <div class="file is-small">
              <label class="file-label is-small">
                <input class="file-input is-small" type="file" name="resume">
                <span class="file-cta is-small">
                  <span class="file-icon is-small">
                    <i class="fas fa-upload"></i>
                  </span>
                  <span class="file-label is-small">
                    Choose a file…
                  </span>
                </span>
              </label>
            </div>
            <p class="help is-warning">Nur RSA-Privatekeys werden unterstützt.</p>
          </div>
        </div>
      </div>

      <button class="button is-small is-primary">Speichern</button>
    </form>
  </div>

  </x-layouts>