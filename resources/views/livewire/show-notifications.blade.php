 <div class="card has-table">
     <header class="card-header">
         <p class="card-header-title">
             <span class="icon"><i class="mdi mdi-ethernet"></i></span>
             {{ __('Notifications') }}
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
                         @foreach ($notifications as $notification)
                             <tr>
                                 <td>{{ $notification->title }}</td>
                                 <td>{{ $notification->message }}</td>
                                 <td>{{ $notification->updated_at }}</td>
                                 <td>
                                     @if (Auth::user()->role >= 1 && $notification->type == 'uplink' && $notification->status == 'waiting')
                                         <form method="POST"
                                             action="{{ route('set-uplink', [$notification->device_id ?? 0]) }}">
                                             @method('PUT')
                                             @csrf
                                             <input type="hidden" value="{{ $notification->id }}" name="id">
                                             <button type="submit" value="yes" name="a"
                                                 class="button no-prevent is-info is-small">Akzeptieren</button>
                                             <button type="submit" name="a" value="no"
                                                 class="ml-2 button no-prevent is-warning is-small">Ablehnen</button>
                                         </form>
                                     @elseif($notification->type == 'uplink')
                                         {{ $notification->status }}
                                     @endif
                                 </td>
                             </tr>
                         @endforeach
                     </tbody>
                 </table>
             </div>
             <div class="p-3">
                 Zeige
                 <select wire:model="numberOfEntries" name="numberOfEntries">
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
