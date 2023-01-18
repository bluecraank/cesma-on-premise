<x-layouts.main>
    @livewire('search-locations')

    @include('modals.LocationAddModal')

    @include('modals.LocationEditModal')

    @include('modals.LocationDeleteModal')
    </x-layouts>
