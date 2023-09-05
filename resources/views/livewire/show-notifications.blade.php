 <div class="card has-table">
     <header class="card-header">
         <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-swap-vertical-bold"></i></span>
             {{ __('Uplink detection') }}
         </p>

     </header>

     <div class="card-content">
         <div class="b-table has-pagination">
             <div class="table-wrapper has-mobile-cards">
                 <table class="is-fullwidth is-striped is-hoverable is-narrow is-fullwidth table">
                     <thead>
                         <tr>
                             <th>{{ __('Title') }}</th>
                             <th>{{ __('Message') }}</th>
                             <th>{{ __('Date') }}</th>
                             <th class="has-text-centered">{{ __('Actions') }}</th>
                         </tr>
                     </thead>
                     <tbody>
                         @if (count($notifications) == 0)
                             <tr>
                                 <td colspan="4" class="has-text-centered">
                                     <span class="icon"><i class="mdi mdi-information-outline"></i></span>
                                     {{ __('No events to review') }}
                                 </td>
                             </tr>
                         @endif
                         @foreach ($notifications as $notification)
                             <tr>
                                 <td>{{ $notification->title }}</td>
                                 <td>{{ $notification->message }}</td>
                                 <td>{{ $notification->updated_at }}</td>
                                 <td class="is-actions-cell has-text-centered">
                                     @if (Auth::user()->role >= 1 && $notification->type == 'uplink' && $notification->status == 'waiting')
                                         <div class="field has-addons has-text-centered">
                                             <div class="control">
                                                 <button type="submit" wire:click="accept({{ $notification->id }})"
                                                     class="button no-prevent is-info is-small">{{ __('Accept') }}</button>
                                             </div>
                                             <div class="control">
                                                 <button type="submit" wire:click="decline({{ $notification->id }})"
                                                     class="button no-prevent is-warning is-small">{{ __('Decline') }}</button>
                                             </div>
                                         </div>
                                     @elseif(Auth::user()->role >= 1 && $notification->type == 'uplink')
                                         {{ $notification->status }}
                                     @elseif($notification->type == 'uplink')
                                         {{ __('No permission') }}
                                     @endif
                                 </td>
                             </tr>
                         @endforeach
                     </tbody>
                 </table>
             </div>
             <div class="p-3">
                 Zeige
                 <select wire:model.live="numberOfEntries" name="numberOfEntries">
                     <option @selected($this->numberOfEntries == 10) value="10">10</option>
                     <option @selected($this->numberOfEntries == 25) value="25">25</option>
                     <option @selected($this->numberOfEntries == 50) value="50">50</option>
                     <option @selected($this->numberOfEntries == 100) value="100">100</option>
                     <option @selected($this->numberOfEntries == '*') value="*">Alle</option>
                 </select>
                 Einträge pro Seite
             </div>
         </div>
     </div>
 </div>
