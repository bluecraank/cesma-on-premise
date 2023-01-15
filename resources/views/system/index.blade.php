<x-layouts.main>
    @livewire('search-user')

    <div class="box">
        <h1 class="title is-pulled-left">{{ __('System.Addititional Pubkeys') }}</h1>
    
        <div class="is-pulled-right ml-4">
            <button onclick="$('.modal-new-key').show()" class="is-small button is-success"><i class="fa-solid fa-plus"></i></button>
        </div>
    
        <div class="is-pulled-right">

        </div>
    
        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>{{ __('System.Desc') }}</th>
                    <th>{{ __('System.Key') }}</th>
                    <th class="has-text-centered">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($keys2 as $key)
                @php
                    $out = strlen($key->key) > 50 ? substr($key->key, 0, 50) . '...' : $key->key;
                @endphp
                    <tr>
                        <td>{{ $key->desc }}</td>
                        <td>{{ $out }}</td>
                        <td class="has-text-centered">
                            <button onclick="$('.modal-delete-key').show();$('.modal-delete-key').find('input.desc').val('{{ $key->desc }}');$('.modal-delete-key').find('input.id').val('{{ $key->id }}')" class="is-small button is-danger"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="/upload/key" class="is-pulled-right is-small mb-5 button is-warning">{{ __('Setup.Privatekey.SSH') }}</a>
        <div class="is-clearfix"></div>
    </div>

    <div class="modal modal-new-user">
        <form action="/user/create" method="post">
            @csrf
            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Modal.User.Add' ) }}</p>
                </header>
                <section class="modal-card-body">
                    <div class="field">
                        <label class="label">{{ __('Modal.User.Add.Name' ) }}</label>
                        <div class="control">
                            <input required class="input" name="name" type="text" placeholder="Username">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">{{ __('Modal.User.Add.Mail' ) }}</label>
                        <div class="control">
                            <input required class="input" name="email" type="email" placeholder="E-Mail">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">{{ __('Modal.User.Add.Password' ) }}</label>
                        <div class="control">
                            <input required class="input" name="password" type="password" placeholder="New password">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">{{ __('Modal.User.Add.PasswordRepeat' ) }}</label>
                        <div class="control">
                            <input required class="input" name="password_confirmation" type="password" placeholder="Confirm password">
                        </div>
                    </div>

                </section>
                <footer class="modal-card-foot">
                    <button class="button is-success">{{ __('Button.Add') }}</button>
                    <button onclick="$('.modal-new-user').hide();return false;" type="button" class="button">{{ __('Button.Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>
    <div class="modal modal-delete-user">
        <form action="/user/delete" method="post">
            @csrf
            <input type="hidden" value="DELETE" name="_method">
            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Modal.User.Delete' ) }}</p>
                </header>
                <section class="modal-card-body">
                    <div class="field">
                        <input type="hidden" name="id" class="user-id" value="">
                        <label class="label">{{ __('Modal.User.Delete.Desc' ) }}</label>
                        <p class="control has-icons-left">
                            <input class="input user-name" type="text" disabled name="name" readonly="true">
                            <span class="icon is-small is-left">
                                <i class="fa fa-a"></i>
                            </span>
                        </p>
                    </div>
                </section>
                <footer class="modal-card-foot">
                    <button class="button is-warning">{{ __('Button.Delete') }}</button>
                    <button onclick="$('.modal-delete-user').hide();return false;" type="button" class="button">{{ __('Button.Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>
    <div class="modal modal-new-key">
        <form action="/pubkey/add" method="post">
            @csrf
            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Modal.Key.Add') }}</p>
                </header>
                <section class="modal-card-body">
                    <div class="field">
                        <label class="label">{{ __('Modal.Key.Add.Name') }}</label>
                        <div class="control">
                            <input required class="input" name="description" type="text" placeholder="Name this Key">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">{{ __('Modal.Key.Add.Pubkey') }}</label>
                        <div class="control">
                            <input required class="input" name="key" type="text" placeholder="ssh-rsa AAACHDK...">
                        </div>
                    </div>
                </section>
                <footer class="modal-card-foot">
                    <button class="button is-success">{{ __('Button.Add') }}</button>
                    <button onclick="$('.modal-new-key').hide();return false;" type="button" class="button">{{ __('Button.Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>
    <div class="modal modal-delete-key">
        <form action="/pubkey/delete" method="post">
            @csrf
            @method('DELETE')
            <input type="hidden" value="" class="id" name="id">
            <div class="modal-background"></div>
            <div style="margin-top: 50px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Modal.Key.Delete') }}</p>
                </header>
                <section class="modal-card-body">
                    <div class="field">
                        <label class="label">{{ __('Modal.Key.Delete.Desc') }}</label>
                        <div class="control">
                            <input required class="input desc" disabled name="name" value="" type="text" placeholder="Name">
                        </div>
                    </div>
                </section>
                <footer class="modal-card-foot">
                    <button class="button is-warning">{{ __('Button.Delete') }}</button>
                    <button onclick="$('.modal-delete-key').hide();return false;" type="button" class="button">{{ __('Button.Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>
    </x-layouts>