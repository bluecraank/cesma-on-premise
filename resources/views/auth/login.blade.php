<x-layouts.header />

<body>
    <div class="set-div-center">
        <div class="column is-12">
            <form method="POST" class="box" action="{{ route('login') }}">
                @csrf

                <h1 style="display:block;margin-bottom: 30px;font-family: 'Kdam Thmor Pro', sans-serif;letter-spacing: -4px;font-size:80px" class="has-text-centered"><i style="font-size:62px" class="fa fa-terminal"></i>cesma</h2>
                    <h1 class="title">{{ __('Login') }}</h1>
                    <h2 class="subtitle">{{ config('app.company') }}</h2>


                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror

                    <div class="field">
                        <label class="label">Username</label>
                        <p class="control has-icons-left">
                            <input required id="username" class="input form-control @error('username') is-invalid @enderror" type="text" value="{{ old('username') }}" name="username">
                            <span class="icon is-small is-left">
                                <i class="fas fa-user"></i>
                            </span>
                        </p>
                    </div>

                    @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror

                    <div class="field">
                        <label class="label">Password</label>
                        <p class="control has-icons-left">
                            <input id="password" type="password" class="input form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" />
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock"></i>
                            </span>
                        </p>
                    </div>
                    <div class="field">
                        <p class="control" style="padding-top:16px">
                            <button type="submit" class="is-fullwidth button is-success">
                                Login
                            </button>
                        </p>
                    </div>
            </form>
        </div>
    </div>

</body>

</html>