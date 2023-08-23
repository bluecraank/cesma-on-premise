<?php

namespace App\Services;

use App\Models\MacType;
use App\Models\MacTypeIcon;
use App\Models\MacVendor;

class MacTypeService
{
    public static function getMacTypes()
    {
        return MacType::all()->sortBy('mac_type');
    }

    public static function getMacTypesList()
    {
        return MacType::all()->sortBy('type')->unique();
    }

    public static function getMacVendors()
    {
        return MacVendor::all()->keyBy('mac_prefix');
    }

    public static function getMacIcons()
    {
        return MacTypeIcon::all();
    }
}
