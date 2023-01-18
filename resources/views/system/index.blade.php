<x-layouts.main>
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

    @include('modals.PubkeyDeleteModal')
    @include('modals.PubkeyAddModal')
    

    </x-layouts>