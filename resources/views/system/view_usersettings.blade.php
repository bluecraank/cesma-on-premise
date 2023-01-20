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
        <form action="/user-settings/update" method="post">
            @csrf
            @method('PUT')
            <div class="columns" style="margin-top: 70px;">
                <div class="column is-12">

                    <div class="subtitle">{{ __('User.ChangeTheme') }}</div>
                    <div class="field">
                        <div class="control">
                            <label class="label is-small">Themes</label>
                            <div class="select is-small is-fullwidth">
                                <select name="theme">
                                    <option
                                        @php echo (isset($_COOKIE['theme']) and $_COOKIE['theme']=='light' ) ? 'selected' : '' @endphp
                                        value="light">Light</option>
                                    <option
                                        @php echo (isset($_COOKIE['theme']) and $_COOKIE['theme']=='dark' ) ? 'selected' : '' @endphp
                                        value="dark">Dark</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="button is-small is-primary">{{ __('Button.Save') }}</button>
        </form>
    </div>
    </x-layouts>
