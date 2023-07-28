<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\CLog;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Building;
use App\Models\Site;
use Illuminate\Support\Facades\Cookie;

class SiteController extends Controller
{
    public function store(StoreSiteRequest $request)
    {
        $newSite = Site::create($request->except('_token', '_method'));

        if ($newSite) {
            CLog::info("Site", "Create site {$request->name}");
            return redirect()->back()->with('success', __('Msg.SiteCreated'));
        }

        CLog::error("Site", "Could not create site {$request->name}");
        return redirect()->back()->withErrors(['message' => 'Site could not be created']);

    }

    public function update(UpdateSiteRequest $request)
    {
        $site = Site::find($request->id);

        if ($site->update($request->except(['_token', '_method']))) {
            CLog::info("Site", "Update site {$request->name}");
            return redirect()->back()->with('success', __('Msg.SiteUpdated'));
        }

        CLog::error("Site", "Could not update site {$request->name}");
        return redirect()->back()->withErrors(['message' => 'Site could not be updated']);
    }

    public function destroy(Request $request)
    {
        $site = Site::find($request->id);

        if ($site->delete()) {
            CLog::info("Site", "Delete site {$request->name}");
            return redirect()->back()->with('success', __('Msg.SiteDeleted'));
        }

        CLog::error("Site", "Could not delete site {$request->name}");
        return redirect()->back()->withErrors(['message' => 'Site could not be deleted']);  
    }

    public function changeSite(Request $request) {
        Cookie::queue(cookie('currentSite', $request->site_id, $minute = 525600));
        sleep(1);

        $url = url()->previous();
        $url = explode('/', $url)[3];

        return redirect('/' . $url);
    }
}
