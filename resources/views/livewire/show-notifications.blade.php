<div class="box">
    <div class="label is-small">Meldungen</div>
    <table style="width:100%" class="mb-4">
        <thead>
            <tr>
                <th>Titel</th>
                <th>Meldung</th>
                <th>Gemeldet</th>
                <th class="has-text-centered">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($notifications as $notification)
                <tr>
                    <td style="padding: 3px 0;">{{ $notification->title }}</td>
                    <td style="padding: 3px 0;">{{ $notification->message }}</td>
                    <td style="padding: 3px 0;">{{ $notification->created_at->diffForHumans() }}</td>
                    <td style="padding: 3px 0;">
                        @if(Auth::user()->role >= 1 && $notification->type == "uplink" && $notification->status == "waiting")
                            <form method="POST" action="{{ route('set-uplink') }}">
                                @method('PUT')
                                @csrf
                                <input type="hidden" value="{{ $notification->id }}" name="id">
                                <button type="submit" value="yes" name="a" class="button no-prevent is-info is-small">Akzeptieren</button>
                                <button type="submit" name="a" value="no" class="ml-2 button no-prevent is-warning is-small">Ablehnen</button>
                            </form>
                        @else
                            {{ $notification->status }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <span class="mr-4">
        Zeige
        <select wire:model="numberOfEntries" name="numberOfEntries">
            <option @selected($this->numberOfEntries == 10) value="10">10</option>
            <option @selected($this->numberOfEntries == 25) value="25">25</option>
            <option @selected($this->numberOfEntries == 50) value="50">50</option>
            <option @selected($this->numberOfEntries == 100) value="100">100</option>
            <option @selected($this->numberOfEntries == "*") value="*">Alle</option>
        </select>
        Einträge pro Seite
    </span>
</div>
