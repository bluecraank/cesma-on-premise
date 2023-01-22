<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/cesma.css">
    <link rel="stylesheet" href="/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

</head>

<body>
    <div style="width:90%;margin: 0 auto;">
    <form method="POST" action="/upload/key/store">
        @csrf
        <div class="field">
            <label class="label">Privatekey</label>
            <div class="control">
                <textarea class="textarea" cols="80" rows="22" name="key" placeholder="-----BEGIN RSA PRIVATE KEY-----
    MY KEY
    -----END RSA PRIVATE KEY-----
    "></textarea>
            </div>
            <p class="has-text-success"><i class="fas fa-lock"></i> Der Privatekey wird sicher verschlüsselt gespeichert</p>

        </div>

        <div class="field">
            <div class="control">
                <button class="button is-link">Upload Privatekey</button>
            </div>
        </div>

    </form>
</div>
</body>
</html>