@section('title', __('Sites'))

<x-layouts.main>
    <div class="box">
        <h1 class="title is-pulled-left">{{ __('Sites') }}</h1>

        <div class="is-pulled-right ml-4">
            @if (Auth::user()->role >= 1)
                <button onclick="$('.modal-add-site').show();return false;" class="is-small button is-success"><i
                        class="fas fa-plus mr-1"></i> {{ __('Button.Create') }}</button>
            @endif
        </div>

        <div class="is-pulled-right">

        </div>

        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Buildings') }}</th>
                    <th>{{ __('Rooms') }}</th>
                    <th>{{ __('Devices') }}</th>
                    <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @if (count($sites) == 0)
                    <tr>
                        <td colspan="5" style="text-align:center">{{ __('No locations found') }}</td>
                    </tr>
                @endif

                @foreach ($sites as $site)
                    <tr>
                        <td>{{ $site->name }}</td>
                        <td>{{ $site->buildings->count() }}</td>
                        <td>{{ $site->rooms->count() }}</td>
                        <td>{{  $site->devices->count() }}</td>
                        <td style="width:150px;">
                            <div class="field has-addons is-justify-content-center">

                                @if (Auth::user()->role >= 1)
                                    <div class="control">
                                        <button
                                            onclick="editLocationModal('{{ $site->id }}', '{{ $site->name }}')"
                                            class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                    </div>
                                    <div class="control">
                                        <button
                                            onclick="deleteLocationModal('{{ $site->id }}', '{{ $site->name }}')"
                                            class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
        </table>
    </div>

    @if (Auth::user()->role >= 1)
        @include('modals.create.CreateSiteModal')
        @include('modals.edit.EditSiteModal')
        @include('modals.delete.DeleteSiteModal')
    @endif
    </x-layouts>
