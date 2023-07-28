@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="pagination">
        @if (!$paginator->onFirstPage())
            <a wire:click="previousPage()" class="pagination-previous">{{ __('Misc.Pagination.Previous') }}</a>
        @endif
        @if ($paginator->hasMorePages())
            <a wire:click="nextPage()" class="pagination-next">{{ __('Misc.Pagination.Next') }}</a>
        @endif
        <ul class="pagination-list">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="pagination-ellipsis">&hellip;</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><a wire:click="gotoPage({{ $page }})" class="pagination-link is-current"
                                    aria-label="Page {{ $page }}" aria-current="page">{{ $page }}</a>
                            </li>
                        @else
                            <li><a wire:click="gotoPage({{ $page }})" class="pagination-link"
                                    aria-label="Goto page {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>
    </nav>
    <span class="mr-4">
        Zeige
        <select wire:model="numberOfEntries" name="numberOfEntries">
            <option @selected($this->numberOfEntries == 10) value="10">10</option>
            <option @selected($this->numberOfEntries == 25) value="25">25</option>
            <option @selected($this->numberOfEntries == 50) value="50" selected>50</option>
            <option @selected($this->numberOfEntries == 100) value="100">100</option>
        </select>
        Einträge pro Seite
    </span>
    <span class="is-pulled-right">Ergebnisse {{ $paginator->firstItem() }} bis {{ $paginator->lastItem() }} von
        {{ $paginator->total() }} Einträgen</span>
@else
    <span class="mr-4">
        Zeige
        <select wire:model="numberOfEntries" name="numberOfEntries">
            <option @selected($this->numberOfEntries == 10) value="10">10</option>
            <option @selected($this->numberOfEntries == 25) value="25">25</option>
            <option @selected($this->numberOfEntries == 50) value="50" selected>50</option>
            <option @selected($this->numberOfEntries == 100) value="100">100</option>
        </select>
        Einträge pro Seite
    </span>
@endif
