@if ($errors->any())
<div class="notification is-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session()->has('success'))
<div class="notification is-success">
    {{ session()->get('success') }}
</div>
@endif

<div class="box">
    <h1 class="title is-pulled-left">Userverwaltung</h1>

    <div class="is-pulled-right ml-4">
        <button onclick="$('.modal-new-user').show()" class="button is-success">Create</button>
    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input" type="text" wire:model.deounce.500ms="searchTerm" placeholder="Search for user...">
                <span class="icon is-small is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
            </div>
        </div>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Name</th>
                <th>Mail</th>
                <th class="has-text-centered">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td style="width:250px;">
                    <div class="has-text-centered">
                        <button disabled onclick="editSwitchModal('{{ $user->id }}', '{{ $user->name }}')" class="button is-info is-small"><i class="fa fa-gear"></i></button>
                        <button onclick="deleteUserModal('{{ $user->id }}', '{{ $user->name }}')" class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>