@section('title', 'Preferences')

<x-layouts.main>
    <div class="box">
        <h1 class="title is-pulled-left">{{ __('Preferences') }}</h1>

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
                <div class="subtitle">{{ __('Change the default theme of cesma') }}</div>
                <div class="field">
                    <div class="control">
                        <label class="label">Themes</label>
                        <div class="select is-fullwidth">
                            <select id="themeSwitch" name="theme">
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
    </div>
    </x-layouts>
