<div class="notification is-response is-{{ $type }}">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <div>
                    @if ($type == 'success')
                        <span class="icon"><i class="mdi mdi-check-circle default"></i></span>
                    @elseif ($type == 'danger')
                        <span class="icon"><i class="mdi mdi-alert-circle default"></i></span>
                    @elseif ($type == 'warning')
                        <span class="icon"><i class="mdi mdi-alert default"></i></span>
                    @elseif ($type == 'info')
                        <span class="icon"><i class="mdi mdi-information default"></i></span>
                    @endif

                    <b>{{ $message }}</b>
                </div>
            </div>
        </div>
    </div>
</div>
