<?php

namespace App\Http\Controllers;

use App\Helper\CLog;
use App\Helper\Utilities;
use Illuminate\Http\Request;
use App\Models\Device;
use phpseclib3\File\ANSI;
use phpseclib3\Net\SSH2;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\PublicKeyLoader;

class SSHController extends Controller
{
    public function store_privatekey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|starts_with:-----BEGIN RSA PRIVATE KEY-----,ends_with:-----END RSA PRIVATE KEY-----'
        ])->validate();

        CLog::info("System", "Import private key");

        if ($validator) {
            $key = Crypt::encrypt(request()->input('key'));
            $save = Storage::disk('local')->put('ssh.key', $key);
            if($save) {
                CLog::info("System", "Import private key successful");
                return "Successfully imported private key";
            }

            return "Could not import private key... Permission denied?";
        } else {
            CLog::error("System", "Import private key failed");
            return "{'message': 'Private key failed validation'}";
        }
    }

    static function performSSH(Request $request)
    {
        $device = Device::find($request->input('id'));

        if(!$device) {
            return json_encode(['status' => 'xmark', 'output' => __('Device not found')], true);
        }

        $command = $request->input('command');

        $return = new \stdClass();
        $return->status = 'check';
        $return->output = '';
        $return->id = $device->id;

        if (Utilities::CheckSSHCommand($command)) {
            $ssh = new SSH2($device->hostname);
            if (config('app.ssh_private_key')) {
                $private_key = Storage::disk('local')->get('ssh.key');

                if(config('app.read-only')[$device->type]) {
                    $return->status = 'xmark';
                    $return->output = __('This device is read-only');
                    return json_encode($return, true);
                }

                if($private_key === NULL) {
                    $return->status = 'xmark';
                    $return->output = __('Use private key enabled, but no key found');
                    return json_encode($return, true);
                }

                $decrypt = Crypt::decrypt(Storage::disk('local')->get('ssh.key'));

                if ($decrypt === NULL) {
                    $return->status = 'xmark';
                    $return->output = __('Private key decryption failed');
                    return json_encode($return, true);
                }

                $key = PublicKeyLoader::load($decrypt);

            } else {
                $key = $request->input('passphrase');
            }


            try {
                if (!$ssh->login(config('app.ssh_username'), $key)) {
                    $return->status = 'xmark';
                    $return->output = __('Login with private key failed');
                    return json_encode($return, true);
                }
            } catch (\Exception $e) {
                $return->status = 'xmark';
                $return->output = __('No connection to device');
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
                    $ssh->read($device->named . "#");
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

                if (SSHController::substr_count_array($output, array("Invalid", "not found", "Incomplete")) != 0) {
                    $return->status = 'xmark';
                    $return->output = $output;
                } else {
                    $return->status = 'check';
                    $return->output = $output;
                }

                CLog::info("SSH", "SSH Command executed", $device, $command);

                return json_encode($return, true);
            }

            $return->status = 'xmark';
            $return->output = 'Connection Failed';

            return json_encode($return, true);
        }

        CLog::error("SSH", "SSH Command not allowed", $device, $command);

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
}
