@section('title', __('Users'))

<div>
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-account"></i></span>
                {{ __('Users') }}
            </p>

            <div class="mr-5 in-card-header-actions">

            </div>
        </header>

        <div class="card-content p-3">
            <div class="b-table">
                <div class="table-wrapper has-mobile-cards">
                    <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>GUID</th>
                                <th>Rolle</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ substr($user->guid, 0, 20) }}...</td>
                                    <td>
                                        @if ($user->role == 0)
                                            {{ __('Read-only user') }}
                                        @elseif($user->role == 1)
                                            Administrator
                                        @else
                                            Super Administrator
                                        @endif
                                    </td>
                                    <td class="is-actions-cell has-text-centered">
                                        @if (Auth::user()->role >= 2)
                                            <div class="field has-addons">
                                                <div class="control">
                                                    <button data-modal="update-user"
                                                        wire:click="show({{ $user->id }}, 'update')"
                                                        class="button is-small is-primary" type="button">
                                                        <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                                    </button>
                                                </div>
                                                <div class="control">
                                                    <button data-modal="delete-user"
                                                        wire:click="show({{ $user->id }}, 'delete')"
                                                        class="button is-small is-danger" type="button">
                                                        <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if (\App\Models\User::count() == 1)
        <div class="notification is-primary">
            <p>{{ __('You are the first user of this application. You automatically got full admin permissions.') }}</p>
        </div>
    @endif

    @if (\App\Models\User::count() <= 3)
        <div class="notification is-info">
            <p>{{ __('To see and assign roles to users, they need to log in for the first time to show up here.') }}</p>
        </div>
    @endif
    @livewire('user-role-modal')
</div>
