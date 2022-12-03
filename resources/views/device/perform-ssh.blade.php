<x-layouts.main>
<div class="columns">
    <div class="column is-4">
      <h1 class="title">Befehl ausführen</h1>
      <form action="" method="post" id="executeForm">
        @csrf
        <div style="margin-top: 25px;">
          <div class="mt-5 field">
            <label class="label">Switch wählen</label>
            <div class="select is-fullwidth">
              <select name="execute-specify-switch" id="">
                <option value="every-switch">Alle Switches</option>
                <option selected value="specific-switch">Switch wählen</option>
                <option value="specific-location">Nach Standort</option>
              </select>
            </div>
          </div>
          <div class="mt-5 field">
            <div class="execute-switch-select">
              <select multiple="multiple" id="switch-select-ms" name="execute-switch-select">
                @foreach ($devices as $device)
                  <option data-location="{{ $device->location }}" value="{{ $device->id }}">{{ $device->name }}</option>
                @endforeach
              </select>
            </div>
  
            <div class="field">
              <div class="location-select control has-icons-left is-hidden">
                <div class="select is-fullwidth">
                  <select required name="execute-switch-select-loc">
                    @foreach ($locations as $location)
                      <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                    </select>
                </div>
                <div class="icon is-small is-left">
                  <i class="fas fa-globe"></i>
                </div>
              </div>
            </div>
          </div>
  
          <input type="hidden" name="which_switch" value="specific-switch">
          <div clas="field">
          </div>
          <div class="mt-5 field">
            <label class="label">Kommando</label>
            <div class="control has-icons-left mb-2">
              <div class="select is-small is-fullwidth">
                <select name="fast-command">
                  <option selected>Schnellaktion</option>
                  <option value="vlan ID tagged PORT">Port an ein VLAN tagged setzen  (vlan ID tagged PORT)</option>
                  <option value="vlan ID untagged PORT">Port an ein VLAN untagged setzen (vlan ID untagged PORT)</option>
                  <option value="vlan ID name NAME">VLAN umbennen (vlan ID name NAME)</option>
                  <option value="show vlan ID">VLAN Infos auflisten (show vlan ID)</option>
                  <option value="show interface PORT">Portinformationen anzeigen (show interface PORT)</option>
                </select>
              </div>
              <div class="icon is-small is-left">
                <i class="fas fa-repeat"></i>
              </div>
            </div>
            <textarea required="true" spellcheck="false" required name="execute-command" class="textarea"
              placeholder="Kommando..."></textarea>
            <p class="help is-danger">Bitte beachte, dass eingegebene Kommandos nicht überprüft werden.</p>
          </div>
          <div class="mt-5 field">
            <label class="label">Passphrase</label>
            <p class="control has-icons-left">
              <input required="true" class="input" type="password" required name="execute-passphrase" placeholder="Passphrase">
              <span class="icon is-small is-left">
                <i class="fas fa-lock"></i>
              </span>
            </p>
          </div>
  
          <button
            onclick="$('.modal-confirmation-command').show();$('.confirmation-content').val($('textarea[name=\'execute-command\']').val());" return false;"
            type="button" style="margin-top: 25px;" class="button is-danger">Ausführen</button>
  
  
          <div style="margin-top: 40px">
            <h2 class="subtitle">Verfügbare Wildcards</h2>
            <table class="table is-striped is-narrow is-fullwidth">
              <thead>
                <tr>
                  <th>Wildcard</th>
                  <th>Funktion</th>
                </tr>
              </thead>
  
              <tbody>
                <tr>
                  <td>{trunks}</td>
                  <td>Ersetzt {trunks} durch die Trunks vom jeweiligen Switch.</td>
                </tr>
                <!-- <tr>
                  <td>{vlans}</td>
                  <td>Ersetzt {vlans} durch die am Standort vorhandenen VLANs.</td>
                </tr> -->
                <tr>
                  <td></td>
                  <td>Kann die Wildcard nicht ersetzt werden, wird der Switch übersprungen.</td>
                </tr>
              </tbody>
            </table>
          </div>
  
          <div class="modal modal-confirmation-command">
            <div class="modal-background"></div>
            <div style="margin-top: 40px" class="modal-card">
              <header class="modal-card-head">
                <p class="modal-card-title">Möchtest du diese Aktion wirklich ausführen?</p>
                <button class="delete" aria-label="close"></button>
              </header>
              <section class="modal-card-body">
                <div class="field">
                  <label class="label">Befehl ausführen:</label>
                  <div class="control">
                    <textarea type="text" disabled class="textarea confirmation-content">
                      </textarea>
                  </div>
                </div>
              </section>
              <footer class="modal-card-foot">
                <button onclick="$('.modal-confirmation-command').hide();return false;" type="button"
                  name="executeSwitchCommand" class="button is-danger">Ausführen</button>
                <button onclick="$('.modal-confirmation-command').hide();return false;" type="button"
                  class="button">Abbrechen</button>
              </footer>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="column is-7 is-offset-1 ajax-results">
      <h1 class="title">Output</h1>
      <div>
        <div class="output-buttons buttons">
  
        </div>
        <div class="outputs">
  
        </div>
      </div>
    </div>
  </div>
</x-layouts>
