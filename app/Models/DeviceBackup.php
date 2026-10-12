<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceBackup extends Model
{

    protected $fillable = [
        'device_id',
        'data',
        'restore_data',
        'status'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function decryptedData(): string
    {
        // Failed backups contain no (encrypted) data
        if (!$this->status) {
            return "Backup failed, no data received";
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decrypt($this->data);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return "Decrypting error (Wrong encryption key?)";
        }
    }
}
