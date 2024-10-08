<!DOCTYPE html>
<html>

<head>
    <title>{{ __('Weekly Backup Report') }}</title>
</head>

<body>
    <style type="text/css">
        .tg {
            border-collapse: collapse;
            border-spacing: 0;
        }

        .tg td {
            border-color: black;
            border-style: solid;
            border-width: 1px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            overflow: hidden;
            padding: 10px 5px;
            word-break: normal;
        }

        .tg th {
            border-color: black;
            border-style: solid;
            border-width: 1px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: normal;
            overflow: hidden;
            padding: 10px 5px;
            word-break: normal;
        }

        .tg .tg-0lax {
            text-align: left;
            vertical-align: top
        }
    </style>
    <h1>{{ __('Weekly Backup Report') }}</h1>
    <table class="tg">
        <thead>
            <tr>
                <th class="tg-0lax">{{ __('Switch') }}</th>
                <th class="tg-0lax">{{ __('Last successful backup') }}</th>
                <th class="tg-0lax">Status<br></th>
                <th class="tg-0lax">{{ __('Site') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($devices as $device)
                @if (isset($device->last_backup->created_at))
                    <tr>
                        <td class="tg-0lax">{{ $device->name }}</td>
                        <td class="tg-0lax">{{ $device->last_backup->created_at }}</td>
                        <td class="tg-0lax">@php
                            if ($device->success_total == 1 and $device->success != 0) {
                                echo "<span style='color:green'>" . __('Successfully') . ' (' . $device->success . '/' . $device->success . ')</span>';
                            } else {
                                echo "<span style='color:red'>" . __('Failed') . ' (' . $device->fail . '/' . ($device->fail + $device->success) . ')</span>';
                            }
                        @endphp</td>
                        <td class="tg-0lax">{{ $device->site }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>

</html>
