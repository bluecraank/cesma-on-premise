@section('title', 'System')

<x-layouts.main>
    <div class="box tab-parent">
        <h1 class="title is-pulled-left">{{ __('System') }}</h1>
        <div class="is-clearfix">

        </div>
        <div class="tabs is-fullwidth">
            <ul>
                <li data-tab="users" class="is-active"
                    onclick="$(this).siblings().removeClass('is-active');$(this).addClass('is-active');$('.tabsbox').addClass('is-hidden');$('.tab-parent').find(`[data-id='users']`).removeClass('is-hidden');">
                    <a>
                        <span class="icon is-small"><i class="fa-solid fa-users"></i></span>
                        <span>Benutzer</span>
                    </a>
                </li>
                <li data-tab="pubkeys"
                    onclick="$(this).siblings().removeClass('is-active');$(this).addClass('is-active');$('.tabsbox').addClass('is-hidden');$('.tab-parent').find(`[data-id='pubkeys']`).removeClass('is-hidden');">
                    <a>
                        <span class="icon is-small"><i class="fa-solid fa-key"></i></span>
                        <span>Öffentliche Schlüssel</span>
                    </a>
                </li>
                <li data-tab="macs"
                    onclick="$(this).siblings().removeClass('is-active');$(this).addClass('is-active');$('.tabsbox').addClass('is-hidden');$('.tab-parent').find(`[data-id='macs']`).removeClass('is-hidden');">
                    <a>
                        <span class="icon is-small"><i class="fa-solid fa-link"></i></span>
                        <span>Maczuordnungen</span>
                    </a>
                </li>
                <li data-tab="snmp"
                    onclick="$(this).siblings().removeClass('is-active');$(this).addClass('is-active');$('.tabsbox').addClass('is-hidden');$('.tab-parent').find(`[data-id='snmp']`).removeClass('is-hidden');">
                    <a>
                        <span class="icon is-small"><i class="fa-solid fa-globe"></i></span>
                        <span>SNMP</span>
                    </a>
                </li>
                {{-- <li data-tab="vorlagen"
                    onclick="$(this).siblings().removeClass('is-active');$(this).addClass('is-active');$('.tabsbox').addClass('is-hidden');$('.tab-parent').find(`[data-id='vorlagen']`).removeClass('is-hidden');">
                    <a>
                        <span class="icon is-small"><i class="fa-solid fa-list-check"></i></span>
                        <span>VLAN-Vorlagen</span>
                    </a>
                </li> --}}
            </ul>
        </div>

        <div class="tabsbox is-hidden" data-id="pubkeys">
            <h1 class="subtitle is-pulled-left">{{ __('System.Addititional Pubkeys') }}</h1>

            <div class="is-pulled-right">
                @if (Auth::user()->role >= 1)
                    <button data-modal="new-key" class="is-small button is-success"><i
                            class="fas fa-plus mr-1"></i> {{ __('Button.Create') }}</button>
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
                            <td style="width:150px;" class="has-text-centered">
                                @if (Auth::user()->role >= 2)
                                    <button
                                        data-modal="delete-key"
                                        onclick="$('.modal-delete-key').find('input.desc').val('{{ $key->description }}');$('.modal-delete-key').find('input.id').val('{{ $key->id }}')"
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
                        <th>{{ __('Role.User') }}</th>
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
                            <td style="width:150px;" class="has-text-centered">
                                @if (Auth::user()->role == 2)
                                    <button
                                        data-modal="edit-user"
                                        onclick="$('.modal-edit-user').find('select').val({{ $user->role }});$('.modal-edit-user').find('input.name').val('{{ $user->name }}');$('.modal-edit-user').find('input.guid').val('{{ $user->guid }}');"
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
                    <button data-modal="add-mac" class="is-small button is-success"><i
                            class="fas fa-plus mr-1"></i> {{ __('Button.Create') }}</button>
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
                        <th style="width:150px;" class="has-text-centered">{{ __('Actions') }}</th>
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
                                    <button class="button is-small is-danger"
                                        data-modal="delete-mac"
                                        onclick="$('.modal-delete-mac').find('.id').val('{{ $mac->id }}');$('.modal-delete-mac').find('.type').val('{{ $mac->type }}');$('.modal-delete-mac').find('.prefix').val('{{ $mac->mac_prefix }}')"><i
                                            class="fas fa-trash"></i></button>
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
                        <th style="width:150px;" class="has-text-centered">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mac_icons as $mac)
                        <tr>
                            <td>{{ $mac->mac_type_id }}</td>
                            <td>{{ $mac->mac_icon ?? '' }}</td>
                            <td class="has-text-centered"><i
                                    class="fas {{ $mac->mac_icon ?? '' }}"></i></td>
                            <td class="has-text-centered">
                                @if (Auth::user()->role >= 1)
                                    {{-- <button class="button is-info is-small"
                                        onclick="$('.modal-edit-icon').show();$('.modal-edit-icon').find('.type').val('{{ $mac->type }}');$('.modal-edit-icon').find('.mac_icon').val('{{ $mac->mac_type_icon()->first()->mac_icon ?? '' }}')"><i
                                            class="fas fa-cog"></i></button> --}}
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
                    <button data-modal="add-router" class="is-small button is-success"><i
                            class="fas fa-plus mr-1"></i> {{ __('Button.Create') }}</button>
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
                        <th style="width:150px;" class="has-text-centered">{{ __('Actions') }}</th>
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
                                                class="button is-info is-small"
                                                onclick='RouterModal("{{ $router->id }}", "{{ $router->ip }}", "{{ $router->desc }}", "modal-edit-router")'><i
                                                    class="fa fa-gear"></i></button>
                                        </div>
                                        <div class="control">
                                            <button title="{{ __('Button.Delete') }}"
                                                class="button is-danger is-small"
                                                onclick='RouterModal("{{ $router->id }}", "{{ $router->ip }}", "{{ $router->desc }}", "modal-delete-router")'>
                                                <i class="fa fa-trash-can"></i></button>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

           <p>Um die Endgeräte besser zuordnen zu können, können hier Routing-Geräte definiert werden, die über SNMP abgefragt werden. Die Abfrage erfolgt über die Community "public"           </p>
        </div>

        <div class="tabsbox is-hidden" data-id="vorlagen">
            <h1 class="subtitle is-pulled-left">Vorlagen</h1>

            <div class="is-pulled-right">
                @if (Auth::user()->role >= 1)
                    <button onclick="$('.modal-add-template').show()" class="is-small button is-success"><i
                            class="fas fa-plus mr-1"></i> {{ __('Button.Create') }}</button>
                @endif
            </div>

            <div class="is-clearfix">

            </div>

            <table class="table is-narrow is-hoverable is-striped is-fullwidth">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Tagged / Allowed</th>
                        <th>Untagged / Native</th>
                        <th style="width:150px;" class="has-text-centered">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($vlan_templates as $template)
                        @php
                            $vlan_ids = json_decode($template->vlans, true);
                            $vlans_vids = [];
                            foreach ($vlan_ids as $vlan_id) {
                                $vlan = \App\Models\Vlan::find($vlan_id);
                                $vlans_vids[] = $vlan->vid ?? '';
                            }
                        @endphp
                        <tr>
                            <td>{{ $template->name }}</td>
                            <td>{{ implode(', ', $vlans_vids) }}</td>
                            <td>{{ $template->untagged }}</td>
                            <td class="has-text-centered">
                                <div class="field has-addons is-justify-content-center">
                                    @if (Auth::user()->role >= 1)
                                        <div class="control">
                                            <button title="{{ __('Switch.Edit.Hint') }}"
                                                class="button is-info is-small"
                                                onclick='VlanTemplateModal("{{ $template->id }}", "{{ $template->name }}", {!! json_encode($template->vlans) !!}, "modal-edit-template")'><i
                                                    class="fa fa-gear"></i></button>
                                        </div>
                                        <div class="control">
                                            <button title="{{ __('Button.Delete') }}"
                                                class="button is-danger is-small"
                                                onclick='VlanTemplateModal("{{ $template->id }}", "{{ $template->name }}", {!! json_encode($template->vlans) !!}, "modal-delete-template")'>
                                                <i class="fa fa-trash-can"></i></button>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            Erstelle Vorlagen um bei der VLAN-Zuordnung von Ports an einem Switch Zeit zu sparen.
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
        @include('modals.combined.VlanTemplateModal')
        @include('modals.combined.SnmpRouterModal')
    @endif

    </x-layouts>
