<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('Weekly Backup Report') }}</title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 14px;">
    {{-- Inline styles only, many mail clients remove <style> blocks --}}
    @php($cell = 'border: 1px solid black; padding: 10px 5px; text-align: left; vertical-align: top; font-family: Arial, sans-serif; font-size: 14px;')
    <h1 style="font-family: Arial, sans-serif;">{{ __('Weekly Backup Report') }}</h1>
    <table style="border-collapse: collapse; border-spacing: 0;">
        <thead>
            <tr>
                <th style="{{ $cell }}">{{ __('Switch') }}</th>
                <th style="{{ $cell }}">{{ __('Last successful backup') }}</th>
                <th style="{{ $cell }}">Status</th>
                <th style="{{ $cell }}">{{ __('Site') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($devices as $device)
                <tr>
                    <td style="{{ $cell }}">{{ $device->name }}</td>
                    <td style="{{ $cell }}">{{ $device->last_backup?->created_at?->format('d.m.Y H:i') ?? '-' }}</td>
                    <td style="{{ $cell }}">
                        @if (!$device->supported)
                            <span style="color:gray">{{ __('Not supported') }}</span>
                        @elseif ($device->success_total == 1)
                            <span style="color:green">{{ __('Successfully') }} ({{ $device->success }}/{{ $device->success }})</span>
                        @elseif ($device->success + $device->fail == 0)
                            <span style="color:red">{{ __('No backup this week') }}</span>
                        @else
                            <span style="color:red">{{ __('Failed') }} ({{ $device->fail }}/{{ $device->fail + $device->success }})</span>
                        @endif
                    </td>
                    <td style="{{ $cell }}">{{ $device->site ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
