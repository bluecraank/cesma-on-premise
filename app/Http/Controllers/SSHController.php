<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Location;
use phpseclib3\File\ANSI;
use phpseclib3\Net\SSH2;
use App\Http\Controllers\EncryptionController;
use Illuminate\Support\Facades\Auth;


class SSHController extends Controller
{
    public function overview()
    {
        $devices = Device::all();
        $locations = Location::all();
        $require_private_key_text = (config('app.ssh_private_key') == "true") ? 'Passphrase für Privatekey' : 'Passwort vom Switch';

        return view('device.perform-ssh', compact('devices', 'locations', 'require_private_key_text'));
    }

    static function performSSH(Request $request) {
        $device = Device::find($request->input('id'));
        $password = $request->input('passphrase');
        $command = $request->input('command');

        $return = new \stdClass();
        $return->status = 'check';
        $return->output = '';
        $return->id = $device->id;

        if(SSHController::checkCommand($command)) {
            $ssh = new SSH2($device->hostname);
            if (config('app.ssh_private_key')) {
                $key = EncryptionController::decryptKey(Auth::user()->privatekey, $request->input('passphrase'));
            } else {
                $key = $request->input('passphrase');
            }

            if (!$ssh->login(config('app.ssh_username'), $key)) {
                $return->status = 'xmark';
                $return->output = 'Login Failed';
                return json_encode($return, true);
            }

            if($ssh->isConnected()) {
                $ssh->setTimeout(3);
                $ssh->setWindowSize(80, 250);

                $output = new ANSI();
                $output->setHistory(250);

                $ssh->read("Press any key to continue");
                $ssh->write("\n");
                $ssh->write("conf\n");

                @$output->appendString($ssh->read());
                
                $ssh->write($command."\n");
                @$output->appendString($ssh->read());
                
                $ssh->write("wr mem\n\n");
                $ssh->disconnect();

                $output = strip_tags(trim(str_replace("\n", "", str_replace("\n\r", "<br>",$output->getHistory()))));
                
                $output = preg_replace('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', '', $output, 1);
                
                if(SSHController::substr_count_array($output, array("Invalid", "not found", "Incomplete")) != 0) {
                    $return->status = 'xmark';
                    $return->output = $output;
                } else {
                    $return->status = 'check';
                    $return->output = $output;
                }

                LogController::log('SSH Befehl', '{"switch": "' . $device->name . '", "command": "' . $command . '"}');


                return json_encode($return, true);
            }

            $return->status = 'xmark';
            $return->output = 'Not connected';
            return json_encode($return, true);
        }

        LogController::log('Blocked SSH Befehl', '{"switch": "' . $device->name . '", "command": "' . $command . '"}');

        $return->output = "Command not allowed";
        $return->status = 'xmark';
        return json_encode($return, true);
    }

    static function substr_count_array( $haystack, $needle ) {
        $count = 0;
        foreach ($needle as $substring) $count += substr_count( strtolower($haystack), strtolower($substring));
        return $count;
   }

    static function checkCommand($command) {

        $blacklisted = array('sh ru', 'aaa');
        foreach($blacklisted as $blacklistedCommand) {
            if(str_contains($command, $blacklistedCommand)) {
                return false;
            }
        }

        return true;
    }
}
