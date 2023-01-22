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
        <div class="columns" style="margin-top: 70px;">
            <div class="column is-12">
                <div class="subtitle">{{ __('User.ChangeTheme') }}</div>
                <div class="field">
                    <div class="control">
                        <label class="label is-small">Themes</label>
                        <div class="select is-small is-fullwidth">
                            <select id="themeSwitch" name="theme">
                                <option
                                    @php echo (isset($_COOKIE['theme']) and $_COOKIE['theme']=='light' ) ? 'selected' : '' @endphp
                                    value="light">Light</option>
                                <option
                                    @php echo (isset($_COOKIE['theme']) and $_COOKIE['theme']=='dark' ) ? 'selected' : '' @endphp
                                    value="dark">Dark</option>
                            </select>
                        </div>
                        <script>
                            var themeSwitch = document.getElementById('themeSwitch');
                            themeSwitch.value = (localStorage.getItem('theme'));

                            themeSwitch.addEventListener('change', function(event) {
                                let theme = $(this).val();
                                if (theme == 'dark') {
                                    document.documentElement.setAttribute('data-theme', 'dark');
                                    localStorage.setItem('theme', 'dark');
                                    switchTheme('dark');

                                } else {
                                    document.documentElement.setAttribute('data-theme', 'light');
                                    localStorage.setItem('theme', 'light');
                                    switchTheme('light');
                                }
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </x-layouts>
