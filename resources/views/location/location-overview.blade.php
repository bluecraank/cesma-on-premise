<x-layouts.main>
    @livewire('search-locations')

    @if (Auth::user()->role == 'admin')
        @include('modals.LocationAddModal')

        @include('modals.LocationEditModal')

        @include('modals.LocationDeleteModal')
    @endif
</x-layouts>
