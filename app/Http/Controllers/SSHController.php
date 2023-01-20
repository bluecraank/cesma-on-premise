<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Location;
use phpseclib3\File\ANSI;
use phpseclib3\Net\SSH2;
use App\Http\Controllers\EncryptionController;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\PublicKeyLoader;

class SSHController extends Controller
{
    public function index()
    {
        $devices = Device::all();
        $locations = Location::all();

        return view('switch.switch-ssh-execute', compact('devices', 'locations'));
    }

    public function encrypt_key_index()
    {
        return view('ssh.encrypt');
    }

    public function encrypt_key_save(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'key' => 'required|starts_with:-----BEGIN RSA PRIVATE KEY-----,ends_with:-----END RSA PRIVATE KEY-----'
        ])->validate();

        if ($validator) {
            $key = EncryptionController::encrypt(request()->input('key'));
            Storage::disk('local')->put('ssh.key', $key);
            return "Importiert";
        } else {
            return "{'error': 'Kein gültiger Schlüssel'}";
        }
    }

    static function performSSH(Request $request)
    {
        $device = Device::find($request->input('id'));
        $command = $request->input('command');

        $user = User::where("api_token", "=", $request->input('api_token'));
        if (!$user) {
            return "Error";
        }
        $return = new \stdClass();
        $return->status = 'check';
        $return->output = '';
        $return->id = $device->id;

        if (SSHController::checkCommand($command)) {
            $ssh = new SSH2($device->hostname);
            if (config('app.ssh_private_key')) {
                $decrypt = EncryptionController::decrypt(Storage::disk('local')->get('ssh.key'));
                if ($decrypt === NULL) {
                    $return->status = 'xmark';
                    $return->output = 'Kein Schlüssel vorhanden';
                    return json_encode($return, true);
                }
                $key = PublicKeyLoader::load($decrypt);
            } else {
                $key = $request->input('passphrase');
            }

            if (!$ssh->login(config('app.ssh_username'), $key)) {
                $return->status = 'xmark';
                $return->output = 'Login Failed';
                return json_encode($return, true);
            }

            if ($ssh->isConnected()) {
                $ssh->setTimeout(3);

                if ($device->type == "aruba-os") {
                    $ssh->setWindowSize(80, 250);
                } else {
                    $ssh->setWindowSize(110, 250);
                }

                $output = new ANSI();
                $output->setHistory(250);

                if ($device->type == "aruba-os") {
                    $ssh->read("Press any key to continue");
                    $ssh->write("\n");
                } else {
                    $ssh->read(json_decode($device->system_data, true)['name'] . "#");
                }

                $ssh->write("conf\n");

                @$output->appendString($ssh->read());

                $ssh->write($command . "\n");

                @$output->appendString($ssh->read());

                if ($device->type == "aruba-os") {
                    $ssh->write("wr mem\n");
                } else {
                    $ssh->write("write memory\n");
                }

                $ssh->disconnect();

                $output = strip_tags(trim(str_replace("\n", "", str_replace("\n\r", "<br>", $output->getHistory()))));

                $output = preg_replace('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', 'SM$$SM', $output, 1);
                $newoutput = strstr($output, 'SM$$SM');
                if ($newoutput != false) {
                    $output = $newoutput;
                }

                if ($device->type == "aruba-os") {
                } else {
                    $oldoutput = $output;
                    $output = strstr($output, json_decode($device->system_data, true)['name'] . "#");
                    if ($output == false) {
                        $output = $oldoutput;
                    }
                }

                if (SSHController::substr_count_array($output, array("Invalid", "not found", "Incomplete")) != 0) {
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

    static function substr_count_array($haystack, $needle)
    {
        $count = 0;
        foreach ($needle as $substring) $count += substr_count(strtolower($haystack), strtolower($substring));
        return $count;
    }

    static function checkCommand($command)
    {

        $blacklisted = array('sh ru', 'aaa', 'no ip', 'no rest-interface', 'no ip ssh');
        foreach ($blacklisted as $blacklistedCommand) {
            if (str_contains($command, $blacklistedCommand)) {
                return false;
            }
        }

        return true;
    }
}
