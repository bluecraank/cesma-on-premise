@inject('DiffClass', 'App\Helper\Diff')
<div class="box">
    <h1 class="title is-pulled-left">{{ __('Header.Log') }}</h1>

    <div class="is-pulled-right ml-4">

    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <div class="select">
                    <select wire:model.debounce.500ms="topic" name="topic" id="">
                        <option value="">Select context</option>
                        @foreach($topics as $topic)
                            <option value="{{ $topic->category }}">{{ $topic->category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <style>
        .log-debug {
            color: #d1bf84;
        }
        .log-info {
            color: #69c78f;
        }
        .log-error {
            color: #c63d3d;
        }
    </style>

<link rel=stylesheet href=https://cdn.jsdelivr.net/npm/pretty-print-json@1.4/dist/css/pretty-print-json.dark-mode.css>
<script src="https://cdn.jsdelivr.net/npm/pretty-print-json@1.4/dist/pretty-print-json.min.js"></script>

<div class="modal is-changes-modal">
    <div class="modal-background"></div>
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Changes made on <span class="topic"></span> at <span class="date"></span></p>
        <button class="delete" aria-label="close" onclick="$('.is-changes-modal').removeClass('is-active')"></button>
      </header>
      <section class="modal-card-body">
        <pre class="changes" style="background:none;"></pre>
      </section>
      {{-- <footer class="modal-card-foot"> --}}
      {{-- </footer> --}}
    </div>
  </div>

    <table class="table is-narrow is-fullwidth">
        <thead>
            <tr>
                <th>Level</th>
                <th>{{ __('Log.User') }}</th>
                <th>{{ __('Log.Action') }}</th>
                <th>Category</th>
                <th style="width:250px;">Additional info</th>
                <th>Switch</th>
                <th style="width:200px;" class="has-text-centered">Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td><span class="log-{{ strtolower($log->level) }}">{{ strtoupper($log->level) }}</span></td>
                    <td>{{ $log->user ?? "No User" }}</td>
                    <td style="width:450px;">{{ $log->description }}</td>
                    <td>
                        {{ $log->category }}
                    </td>
                    <td style="width:250px;">
                        {{ $DiffClass::visualize($log->additional_info, $log) }}
                    </td>
                    <td>
                        {{ $log->device_name }}
                    </td>
                    <td class="has-text-centered">{{ $log->created_at->format('m/d/Y H:i:s') }}</td>

                </tr>
            @endforeach
    </table>
    <div>
        {{ $logs->links('pagination::default') }}
    </div>
</div>