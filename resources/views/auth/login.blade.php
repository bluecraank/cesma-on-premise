<x-layouts.guest>

    @section('content')
        <div class="container">
            <div style="height:100vh;align-items:center;" class="columns">
                <div class="column is-4 is-offset-4">
                    <div class="card">
                        <header class="card-header" style="justify-content: center">
                            <div class="has-text-centered">
                                <span class="has-kdam-pro-text is-size-1"><span class="mdi mdi-console-line"></span>
                                    cesma</span>
                            </div>
                        </header>
                        <div class="card-content">
                            @if ($errors->any())
                                <div class="notification is-response is-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="field is-horizontal">
                                    <div class="field-body">
                                        <div class="field">
                                            <p class="control is-expanded has-icons-left">
                                                <input value="{{ old('username') }}" placeholder="{{ __('Username') }}"
                                                    class="input @error('username') is-danger @enderror" type="text"
                                                    placeholder="" name="username" autocomplete="username" required
                                                    autofocus>
                                                <span class="icon is-small is-left"><i class="mdi mdi-account"></i></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="field is-horizontal">
                                    <div class="field-body">
                                        <div class="field">
                                            <p class="control is-expanded has-icons-left has-icons-right">
                                                <input class="input @error('password') is-danger @enderror" type="password"
                                                    placeholder="{{ __('Password') }}" name="password" required
                                                    autocomplete="current-password">
                                                <span class="icon is-small is-left"><i class="mdi mdi-lock"></i></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="field is-horizontal">
                                    <div class="field-body">
                                        <div class="field">
                                            <div class="control">
                                                <label class="b-checkbox checkbox"><input type="checkbox" name="remember"
                                                        {{ old('remember') ? 'checked' : '' }}>
                                                    <span class="check is-primary"></span>
                                                    <span class="control-label">{{ __('Remember me') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="field is-horizontal">
                                    <div class="field-body">
                                        <div class="field">
                                            <div class="field is-grouped">
                                                <div class="control">
                                                    <button type="submit" class="no-prevent button is-primary">
                                                        <span>{{ __('Sign in') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-layouts.guest>
