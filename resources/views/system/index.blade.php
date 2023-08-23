@section('title', 'System')

<x-layouts.main>
    <div class="box tab-parent">
        <h1 class="title is-pulled-left">{{ __('System') }}</h1>
        <div class="is-clearfix">

        </div>
        <div id="systemTabList" class="tabs is-fullwidth">
            <ul>
                <li data-tab="users" class="systemTab is-active">
                    <a>
                        <span class="icon is-small"><i class="fa-solid fa-users"></i></span>
                        <span>Benutzer</span>
                    </a>
                </li>
                <li data-tab="pubkeys" class="systemTab">
                    <a>
                        <span class="icon is-small"><i class="fa-solid fa-key"></i></span>
                        <span>Öffentliche Schlüssel</span>
                    </a>
                </li>
                <li data-tab="macs" class="systemTab">
                     <a>
                        <span class="icon is-small"><i class="fa-solid fa-link"></i></span>
                        <span>MAC-Adresszuordnungen</span>
                    </a>
                </li>
                <li data-tab="snmp" class="systemTab">
                    <a>
                        <span class="icon is-small"><i class="fa-solid fa-globe"></i></span>
                        <span>SNMP</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="tabsbox is-hidden" data-id="pubkeys">
            <h1 class="subtitle is-pulled-left">{{ __('System.Addititional Pubkeys') }}</h1>

            <div class="is-pulled-right">
                @if (Auth::user()->role >= 1)
                    <button data-modal="new-key" class="is-small button is-success"><i class="fas fa-plus mr-1"></i>
                        {{ __('Create') }}</button>
                @endif
            </div>

            <div class="is-clearfix"></div>

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
                            $decrypted_key = Crypt::decrypt($key->key);
                            $out = strlen($decrypted_key) > 50 ? substr($decrypted_key, 0, 50) . '...' : $decrypted_key;
                        @endphp
                        <tr>
                            <td>{{ $key->description }}</td>
                            <td>{{ $out }}</td>
                            <td class="is-actions-cell has-text-centered">
                                @if (Auth::user()->role >= 2)
                                    <button data-modal="delete-key"
                                        data-id="{{ $key->id }}"
                                        data-description="{{ $key->description }}"
                                        class="is-small button is-danger"><i class="fas fa-trash"></i></button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if (Auth::user()->role == 2)
                <a href="/privatekey"
                    class="is-pulled-right is-small mb-5 button is-warning">{{ __('Setup.Privatekey.SSH') }}</a>
                <div class="is-clearfix"></div>
            @endif
        </div>

        <div class="tabsbox" data-id="users">
            <h1 class="subtitle is-pulled-left">{{ __('System.User') }}</h1>

            <div class="is-clearfix">

            </div>

            <table class="table is-narrow is-hoverable is-striped is-fullwidth">
                <thead>
                    <tr>
                        <th>{{ __('User') }}</th>
                        <th>GUID</th>
                        <th>{{ __('Role') }}</th>
                        <th class="has-text-centered">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ substr($user->guid, 0, 20) }}...</td>
                            <td>{{ $user->getRoleName() }}</td>
                            <td class="is-actions-cell has-text-centered">
                                @if (Auth::user()->role == 2)
                                    <button data-modal="edit-user"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-guid="{{ $user->guid }}"
                                        data-role="{{ $user->role }}"
                                        data-sites="{{ $user->allowed_sites }}"
                                        class="is-small button is-info"><i class="fas fa-cog"></i></button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="tabsbox is-hidden" data-id="macs">
            <h1 class="subtitle is-pulled-left">{{ __('System.MacType') }}</h1>

            <div class="is-pulled-right">
                @if (Auth::user()->role >= 1)
                    <button data-modal="add-mac" class="is-small button is-success"><i class="fas fa-plus mr-1"></i>
                        {{ __('Create') }}</button>
                @endif
            </div>

            <div class="is-clearfix"></div>


            <table class="table is-narrow is-hoverable is-striped is-fullwidth">
                <thead>
                    <tr>
                        <th>MAC Prefix</th>
                        <th>Vendor</th>
                        <th>{{ __('System.Desc') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th class="is-actions-cell has-text-centered">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mac_prefixes as $mac)
                        <tr>
                            <td>{{ $mac->mac_prefix }}</td>
                            <td>{{ $mac_vendors[$mac->mac_prefix]->vendor_name ?? 'Unknown' }}</td>
                            <td>{{ $mac->description }}</td>
                            <td>{{ $mac->type }}</td>
                            <td class="has-text-centered">
                                @if (Auth::user()->role >= 1)
                                    <button class="button is-small is-danger" data-modal="delete-mac"
                                        data-id="{{ $mac->id }}" data-type="{{ $mac->type }}"
                                        data-prefix="{{ $mac->mac_prefix }}"><i class="fas fa-trash"></i></button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h1 class="subtitle is-pulled-left">{{ __('System.MacIcon') }}</h1>

            <div class="is-clearfix">

            </div>

            <table class="table is-narrow is-hoverable is-striped is-fullwidth">
                <thead>
                    <tr>
                        <th>MAC {{ __('Type') }}</th>
                        <th>Font Awesome Icon</th>
                        <th class="has-text-centered">{{ __('Preview') }}</th>
                        <th class="is-actions-cell has-text-centered">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mac_icons as $mac)
                        <tr>
                            <td>{{ $mac->mac_type }}</td>
                            <td>{{ $mac->mac_icon ?? '' }}</td>
                            <td class="has-text-centered"><i class="fas {{ $mac->mac_icon ?? '' }}"></i></td>
                            <td class="has-text-centered">
                                @if (Auth::user()->role >= 1)
                                    <button class="button is-info is-small" data-modal="edit-icon"
                                        data-mac_type="{{ $mac->mac_type }}"
                                        data-mac_icon="{{ $mac->mac_icon ?? '' }}"><i class="fas fa-cog"></i></button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="tabsbox is-hidden" data-id="snmp">
            <h1 class="subtitle is-pulled-left">SNMP</h1>

            <div class="is-pulled-right">
                @if (Auth::user()->role >= 1)
                    <button data-modal="add-router" class="is-small button is-success"><i class="fas fa-plus mr-1"></i>
                        {{ __('Create') }}</button>
                @endif
            </div>

            <div class="is-clearfix">

            </div>


            <table class="table is-narrow is-hoverable is-striped is-fullwidth">
                <thead>
                    <tr>
                        <th>Hostname/IP</th>
                        <th>Beschreibung</th>
                        <th>Status</th>
                        <th class="is-actions-cell has-text-centered">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($routers as $router)
                        <tr>
                            <td>{{ $router->ip }}</td>
                            <td>{{ $router->desc }}</td>
                            <td>{!! $router->check
                                ? '<span class="has-text-success">Connection successfully</span>'
                                : '<span class="has-text-danger">No Connection</span>' !!}</td>
                            <td>
                                <div class="field has-addons is-justify-content-center">
                                    @if (Auth::user()->role >= 1)
                                        <div class="control">
                                            <button title="{{ __('Switch.Edit.Hint') }}"
                                                class="button is-info is-small" data-modal="edit-router"
                                                data-id="{{ $router->id }}" data-ip="{{ $router->ip }}"
                                                data-desc="{{ $router->desc }}"><i class="fa fa-gear"></i></button>
                                        </div>
                                        <div class="control">
                                            <button title="{{ __('Delete') }}"
                                                class="button is-danger is-small" data-modal="delete-router"
                                                data-id="{{ $router->id }}" data-ip="{{ $router->ip }}"
                                                data-desc="{{ $router->desc }}">
                                                <i class="fa fa-trash-can"></i></button>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p>Um die Endgeräte besser zuordnen zu können, können hier Routing-Geräte definiert werden, die über SNMP
                abgefragt werden. Die Abfrage erfolgt über die Community "public" </p>
        </div>

        <script>
            $(document).ready(function() {
                if ("{{ old('last_tab') }}" != "") {
                    $('.tabs li').removeClass('is-active');
                    $('.tabsbox').addClass('is-hidden');
                    $('.tabs li[data-tab="{{ old('last_tab') }}"]').addClass('is-active');
                    $('.tabsbox[data-id="{{ old('last_tab') }}"]').removeClass('is-hidden');
                }
            });
        </script>
    </div>

    @if (Auth::user()->role >= 1)
        @include('modals.PubkeySyncModal')
        @include('modals.delete.PubkeyDeleteModal')
        @include('modals.create.PubkeyAddModal')
        @include('modals.edit.UserEditModal')
        @include('modals.create.MacTypeAddModal')
        @include('modals.create.MacTypeIconAddModal')
        @include('modals.delete.MacTypeDeleteModal')
        @include('modals.combined.SnmpRouterModal')
    @endif

    </x-layouts>
