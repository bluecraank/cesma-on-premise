<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBackupRequest;
use App\Http\Requests\UpdateBackupRequest;
use App\Mail\SendBackupStatus;
use App\Models\Backup;
use phpseclib3\Net\SFTP;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use phpseclib3\Crypt\PublicKeyLoader;

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $backups = Backup::all()->sortByDesc('created_at')->keyBy('id')->take(Device::all()->count());
        $devices = Device::all()->keyBy('id');
        return view('backups.index', compact('backups', 'devices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBackupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBackupRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Backup  $backup
     * @return \Illuminate\Http\Response
     */
    public function show(Backup $backup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Backup  $backup
     * @return \Illuminate\Http\Response
     */
    public function edit(Backup $backup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBackupRequest  $request
     * @param  \App\Models\Backup  $backup
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBackupRequest $request, Backup $backup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Backup  $backup
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $find = Backup::find($request->input('id'));
        if($find->delete()) {
            LogController::log('Backup gelöscht', '{"id": "' . $request->id . '", "device_id": "' . $find->device_id . '", "created_at": "' . $find->created_at . '"}');
            return redirect()->back()->with('success', 'Backup deleted successfully');
        }
        
        return redirect()->back()->withErrors(['error' => 'Backup could not be deleted']);

    }

    public function getSwitchBackups($id) {
        $backups = Backup::where('device_id', $id)->get()->sortByDesc('created_at')->keyBy('id');
        $device = Device::find($id);
        return view('backups.switch-backups', compact('backups', 'device'));
    }

    static function getBackups() {
        Device::all()->each(function ($device) {
            if (config('app.ssh_private_key')) {
                $decrypt = file_get_contents($_SERVER['DOCUMENT_ROOT']."storage/app/ssh_private.key");
                if($decrypt !== NULL) {
                    $key = PublicKeyLoader::load($decrypt);
                } else {
                    return false;
                }
            } else {
                $key = EncryptionController::decrypt($device->password);
            }
            
            try {
                $sftp = new SFTP($device->hostname);
                $sftp->login(config('app.ssh_username'), $key);
                $data = $sftp->get('/cfg/running-config');
        
                if($data === NULL or strlen($data) < 10 or $data == false) {
                    Backup::create([
                        'device_id' => $device->id,
                        'data' => "No data received",
                        'status' => 0,
                    ]);
                } elseif(strlen($data) > 10) {
                    Backup::create([
                        'device_id' => $device->id,
                        'data' => $data,
                        'status' => 1,
                    ]);
                }
            } catch (\Exception $e) {
                Backup::create([
                    'device_id' => $device->id,
                    'data' => $e->getMessage(),
                    'status' => 0,
                ]);
            }
        });
    }

    public function downloadBackup($id) {
        $backup = Backup::find($id);
        $device = Device::find($backup->device_id);
        $filename = $device->name . '_' . $backup->created_at->format('Y-m-d_H-i-s') . '_BACKUP.txt';
        return response($backup->data, 200)->header('Content-Type', 'text/plain')->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    static function sendMail() {

        $backups = Backup::all()->keyBy('id');
        $devices = Device::all()->keyBy('id');

        $modDevices = [];
        $totalError = true;

        foreach($devices as $key => $device) {
            $modDevices[$key] = new \stdClass();
            $modDevices[$key]->name = $device->name;
            $modDevices[$key]->backups = $backups->where('device_id', $device->id)->count();
            $modDevices[$key]->last_backup = $backups->where('device_id', $device->id)->last();
            $modDevices[$key]->success = $backups->where('device_id', $device->id)->where('status', 1)->where('created_at', '>', Carbon::now()->startOfWeek())->where('created_at', '<', Carbon::now()->endOfWeek())->count();
            $modDevices[$key]->fail = $backups->where('device_id', $device->id)->where('status', 0)->where('created_at', '>', Carbon::now()->startOfWeek())->where('created_at', '<', Carbon::now()->endOfWeek())->count();
            $modDevices[$key]->success_total = ($modDevices[$key]->fail == 0) ? 1 : 0;

            if($modDevices[$key]->success_total == 0) {
                $totalError = false;
            }
        }

        //ddd($modDevices);
        Mail::to('fischers@doepke.de')->send(new SendBackupStatus($backups, $modDevices, $totalError));

        dd('Success! Email has been sent successfully.');
    }
}

