<x-layouts.main>
    <div class="box">
        <h1 class="title is-pulled-left">{{ __('System.Addititional Pubkeys') }}</h1>

        <div class="is-pulled-right ml-4">
            @if (Auth::user()->role == 'admin')
                <button onclick="$('.modal-new-key').show()" class="is-small button is-success"><i
                        class="fas fa-plus"></i></button>
            @endif
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
                @foreach ($keys as $key)
                    @php
                        $out = strlen($key->key) > 50 ? substr($key->key, 0, 50) . '...' : $key->key;
                    @endphp
                    <tr>
                        <td>{{ $key->desc }}</td>
                        <td>{{ $out }}</td>
                        <td style="width:150px;" class="has-text-centered">
                            @if (Auth::user()->role == 'admin')
                                <button
                                    onclick="$('.modal-delete-key').show();$('.modal-delete-key').find('input.desc').val('{{ $key->desc }}');$('.modal-delete-key').find('input.id').val('{{ $key->id }}')"
                                    class="is-small button is-danger"><i class="fas fa-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if (Auth::user()->role == 'admin')
            <a href="/upload/key"
                class="is-pulled-right is-small mb-5 button is-warning">{{ __('Setup.Privatekey.SSH') }}</a>
            <div class="is-clearfix"></div>
        @endif
    </div>

    <div class="box">
        <h1 class="title is-pulled-left">{{ __('System.User') }}</h1>

        <div class="is-pulled-right ml-4">

        </div>

        <div class="is-pulled-right">

        </div>

        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>User</th>
                    <th>GUID</th>
                    <th>Role</th>
                    <th class="has-text-centered">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ substr($user->guid, 0, 20) }}...</td>
                        <td>{{ $user->role }}</td>
                        <td style="width:150px;" class="has-text-centered">
                            @if (Auth::user()->role == 'admin')
                                <button
                                    onclick="$('.modal-edit-user').show();$('.modal-edit-user').find('option.{{ $user->role }}').prop('selected', 'true');$('.modal-edit-user').find('input.name').val('{{ $user->name }}');$('.modal-edit-user').find('input.guid').val('{{ $user->guid }}');"
                                    class="is-small button is-info"><i class="fas fa-cog"></i></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="box">
        <h1 class="title is-pulled-left">{{ __('System.MacType') }}</h1>

        <div class="is-pulled-right ml-4">

        </div>

        <div class="is-pulled-right">
            @if (Auth::user()->role == 'admin')
                <button onclick="$('.modal-add-mac').show()" class="is-small button is-success"><i
                        class="fas fa-plus"></i></button>
            @endif
        </div>

        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>MAC Prefix</th>
                    <th>MAC Vendor</th>
                    <th>Beschreibung</th>
                    <th>Typ</th>
                    <th style="width:150px;" class="has-text-centered">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($macs as $mac)
                    <tr>
                        <td>{{ $mac->mac_prefix }}</td>
                        <td>{{ $vendors[$mac->mac_prefix]->vendor_name }}</td>
                        <td>{{ $mac->mac_desc }}</td>
                        <td>{{ $mac->mac_type }}</td>
                        <td class="has-text-centered">
                            @if (Auth::user()->role == 'admin')
                                <button class="button is-small is-danger" onclick="$('.modal-delete-mac').show();$('.modal-delete-mac').find('.id').val('{{ $mac->id }}');$('.modal-delete-mac').find('.type').val('{{ $mac->mac_type }}');$('.modal-delete-mac').find('.prefix').val('{{ $mac->mac_prefix }}')"><i class="fas fa-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="box">
        <h1 class="title is-pulled-left">{{ __('System.MacIcon') }}</h1>

        <div class="is-pulled-right ml-4">

        </div>

        <div class="is-pulled-right">
            @if (Auth::user()->role == 'admin')
                <button onclick="$('.modal-add-mac').show()" class="is-small button is-success"><i
                        class="fas fa-plus"></i></button>
            @endif
        </div>

        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>MAC Typ</th>
                    <th>Font Awesome Icon</th>
                    <th class="has-text-centered">Vorschau</th>
                    <th style="width:150px;" class="has-text-centered">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($types as $mac)
                    <tr>
                        <td>{{ $mac }}</td>
                        <td>{{ isset($icons[$mac]) ? $icons[$mac]->mac_icon : '' }}</td>
                        <td class="has-text-centered"><i
                                class="fas {{ isset($icons[$mac]) ? $icons[$mac]->mac_icon : '' }}"></i></td>
                        <td class="has-text-centered">
                            @if (Auth::user()->role == 'admin')
                                <button class="button is-info is-small"
                                    onclick="$('.modal-edit-icon').show();$('.modal-edit-icon').find('.type').val('{{ $mac }}');$('.modal-edit-icon').find('.mac_icon').val('{{ isset($icons[$mac]) ? $icons[$mac]->mac_icon : 'fa-' }}')"><i
                                        class="fas fa-cog"></i></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (Auth::user()->role == 'admin')
        @include('modals.PubkeySyncModal')
        @include('modals.PubkeyDeleteModal')
        @include('modals.PubkeyAddModal')
        @include('modals.UserEditModal')
        @include('modals.MacFilterAddModal')
        @include('modals.MacFilterIconAddModal')
        @include('modals.MacFilterDeleteModal')
    @endif

    </x-layouts>
