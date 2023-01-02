<!DOCTYPE html>
<html>
<head>
    <title>Wöchentlicher Bericht Backups Switche</title>
</head>
<body>
    <style type="text/css">
        .tg  {border-collapse:collapse;border-spacing:0;}
        .tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
          overflow:hidden;padding:10px 5px;word-break:normal;}
        .tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
          font-weight:normal;overflow:hidden;padding:10px 5px;word-break:normal;}
        .tg .tg-0lax{text-align:left;vertical-align:top}
        </style>
    <h1>Wöchentlicher Bericht Backups Switche</h1>
        <table class="tg">
            <thead>
                <tr>
                  <th class="tg-0lax">Name</th>
                  <th class="tg-0lax">Letztes erfolgreiches Backup</th>
                  <th class="tg-0lax">Status<br></th>
                </tr>
            </thead>
            <tbody>
              <tr>
                @foreach($devices as $device)
                <td class="tg-0lax">{{ $device->name }}</td>
                <td class="tg-0lax">{{ $device->last_backup->created_at }}</td>
                <td class="tg-0lax">@php if($device->success_total == 1) { echo "<span style='color:green'>Erfolgreich (".$device->success."/".$device->success.")</span>"; } else { echo "<span style='color:red'>Fehlgeschlagen (".$device->fail."/".(($device->fail)+($device->success)).")</span>"; }  @endphp</td>
                @endforeach
              </tr>
            </tbody>
        </table>
</body>
</html>