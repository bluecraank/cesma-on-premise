<x-layouts.main>
    @livewire('search-vlans')

    @if (Auth::user()->role == 'admin')
        <div class="box">
            <div class="label is-small">Alle Switche</div>
            <div class="buttons are-small">
                <form action="post" id="form-all-devices">
                    @csrf
                    <a onclick="$('.modal-sync-vlans').show();return false;" class="button is-info"><i
                            class="fa-solid fa-ethernet mr-2"></i> Sync VLANs</a>
                </form>
            </div>
        </div>

        <article class="message is-info">
            <div class="message-header">
              <p>Info</p>
              <button class="delete" aria-label="delete"></button>
            </div>
            <div class="message-body">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. <strong>Pellentesque risus mi</strong>, tempus quis placerat ut, porta nec nulla. Vestibulum rhoncus ac ex sit amet fringilla. Nullam gravida purus diam, et dictum <a>felis venenatis</a> efficitur. Aenean ac <em>eleifend lacus</em>, in mollis lectus. Donec sodales, arcu et sollicitudin porttitor, tortor urna tempor ligula, id porttitor mi magna a neque. Donec dui urna, vehicula et sem eget, facilisis sodales sem.
            </div>
          </article>

        @include('modals.VlanAddModal')

        @include('modals.VlanEditModal')

        @include('modals.VlanDeleteModal')

        @include('modals.VlanSyncModal')
    @endif

    </x-layouts>
