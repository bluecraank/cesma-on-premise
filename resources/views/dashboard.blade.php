@section('title', 'Test')


<x-layouts.main>

        @include('components.notification', ['message' => 'Responsive table', 'type' => 'success'])

        <div class="card has-table">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-account-multiple"></i></span>
                    Clients
                </p>
                <a href="#" class="card-header-icon">
                    <span class="icon"><i class="mdi mdi-reload"></i></span>
                </a>
            </header>

            <div class="card-content">
                <div class="b-table has-pagination">
                    <div class="table-wrapper has-mobile-cards">
                        <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                            <thead>
                                <tr>
                                    <th class="is-checkbox-cell">
                                        <label class="b-checkbox checkbox">
                                            <input type="checkbox" value="false">
                                            <span class="check"></span>
                                        </label>
                                    </th>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>City</th>
                                    <th>Progress</th>
                                    <th>Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="is-checkbox-cell">
                                        <label class="b-checkbox checkbox">
                                            <input type="checkbox" value="false">
                                            <span class="check"></span>
                                        </label>
                                    </td>
                                    <td class="is-image-cell">
                                        <div class="image">
                                            <img src="https://avatars.dicebear.com/v2/initials/rebecca-bauch.svg"
                                                class="is-rounded">
                                        </div>
                                    </td>
                                    <td data-label="Name">Rebecca Bauch</td>
                                    <td data-label="Company">Daugherty-Daniel</td>
                                    <td data-label="City">South Cory</td>
                                    <td data-label="Progress" class="is-progress-cell">
                                        <progress max="100" class="progress is-small is-primary"
                                            value="79">79</progress>
                                    </td>
                                    <td data-label="Created">
                                        <small class="has-text-grey is-abbr-like" title="Oct 25, 2020">Oct 25,
                                            2020</small>
                                    </td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="buttons is-right">
                                            <button class="button is-small is-primary" type="button">
                                                <span class="icon"><i class="mdi mdi-eye"></i></span>
                                            </button>
                                            <button class="button is-small is-danger jb-modal"
                                                data-target="sample-modal" type="button">
                                                <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="is-checkbox-cell">
                                        <label class="b-checkbox checkbox">
                                            <input type="checkbox" value="false">
                                            <span class="check"></span>
                                        </label>
                                    </td>
                                    <td class="is-image-cell">
                                        <div class="image">
                                            <img src="https://avatars.dicebear.com/v2/initials/felicita-yundt.svg"
                                                class="is-rounded">
                                        </div>
                                    </td>
                                    <td data-label="Name">Felicita Yundt</td>
                                    <td data-label="Company">Johns-Weissnat</td>
                                    <td data-label="City">East Ariel</td>
                                    <td data-label="Progress" class="is-progress-cell">
                                        <progress max="100" class="progress is-small is-primary"
                                            value="67">67</progress>
                                    </td>
                                    <td data-label="Created">
                                        <small class="has-text-grey is-abbr-like" title="Jan 8, 2020">Jan 8,
                                            2020</small>
                                    </td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="buttons is-right">
                                            <button class="button is-small is-primary" type="button">
                                                <span class="icon"><i class="mdi mdi-eye"></i></span>
                                            </button>
                                            <button class="button is-small is-danger jb-modal"
                                                data-target="sample-modal" type="button">
                                                <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <b>Backups diese woche</b><br>
        <b>vlans zu ports</b><br>
        <b>clients zu vlan</b><br>
        <b>weiter Statistiken?</b>

    </x-layouts>
