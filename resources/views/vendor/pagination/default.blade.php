@if ($paginator->hasPages())
    <div class="notification m-0">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <div class="buttons has-addons">
                        @foreach ($elements as $element)
                            @if (is_string($element))
                                <button type="button" class="button">...</button>
                            @endif

                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <button wire:click="gotoPage({{ $page }})" type="button"
                                            class="button is-active">{{ $page }}</button>
                                    @else
                                        <button wire:click="gotoPage({{ $page }})" type="button"
                                            class="button">{{ $page }}</button>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <small>{{ __('Results') }} {{ $paginator->firstItem() }} {{ __('to') }} {{ $paginator->lastItem() }} {{ __('of') }}
                        {{ $paginator->total() }} {{ __('entries') }}</small>
                </div>
            </div>
        </div>
        <span class="mr-4 m-2 is-block">
            {{ __('Show') }}
            <select wire:model="numberOfEntries" wire:change="updateNumberOfEntries" name="numberOfEntries">
                <option @selected($this->numberOfEntries == 10) value="10">10</option>
                <option @selected($this->numberOfEntries == 25) value="25">25</option>
                <option @selected($this->numberOfEntries == 50) value="50" selected>50</option>
                <option @selected($this->numberOfEntries == 100) value="100">100</option>
            </select>
            {{ __('entries per page') }}
        </span>
    </div>

@else
<div class="notification m-0">
    <span class="mr-4 m-2 is-block">
        {{ __('Show') }}
        <select wire:model="numberOfEntries" wire:change="updateNumberOfEntries" name="numberOfEntries">
            <option @selected($this->numberOfEntries == 10) value="10">10</option>
            <option @selected($this->numberOfEntries == 25) value="25" selected>25</option>
            <option @selected($this->numberOfEntries == 50) value="50">50</option>
            <option @selected($this->numberOfEntries == 100) value="100">100</option>
        </select>
        {{ __('entries per page') }}
    </span>
</div>
@endif
