<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\CLog;
use App\Http\Requests\StoreSiteRequest;
use App\Models\Site;
use Illuminate\Support\Facades\Cookie;

class SiteController extends Controller
{
    public function store(StoreSiteRequest $request)
    {
        $newSite = Site::create($request->except('_token', '_method'));

        if ($newSite) {
            CLog::info("Site", "Site created", null, $request->name);
            return redirect()->back()->with('success', __('Site ":name" successfully created', ['name' => $request->name]));
        }

        CLog::error("Site", "Could not create site {$request->name}");
        return redirect()->back()->withErrors(['message' => 'Site could not be created']);

    }

    public function changeSite(Request $request) {
        if($request->has('site_id') && Site::where('id', $request->site_id)->exists()) {
            Cookie::queue(cookie('currentSite', $request->site_id, $minute = 525600));
            sleep(1);

            $url = url()->previous();
            $url = explode('/', $url)[3];

            return redirect()->route($url != "" ? $url : 'dashboard');
        }

        return redirect()->back();
    }
}
