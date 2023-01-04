<form method="POST" action="/encrypt-key/save">
    @csrf
    <div class="field">
        <label class="label">Passphrase</label>
        <div class="control">
            <input class="input" type="text" name="passphrase" placeholder="Passphrase">
        </div>
    </div>

    <div class="field">
        <label class="label">Key</label>
        <div class="control">
            <textarea class="textarea" name="key" placeholder="Key"></textarea>
        </div>
    </div>
    
    <div class="field">
        <div class="control">
            <button class="button is-link">Submit</button>
        </div>
    </div>

    @php 
        if(isset($key) and $key) echo '<pre width="100vh">' . $key . '</pre>';
    @endphp
</form>