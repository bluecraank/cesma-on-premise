<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\CLog;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Building;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index() {
        $site = Auth::user()->currentSite();
        $rooms = $site->rooms;
        $buildings = $site->buildings;

        return view('room.index', [
            'rooms' => $rooms,
            'buildings' => $buildings
        ]);
    }

    public function store(StoreRoomRequest $request)
    {
        if (Room::create($request->except('_token', '_method'))) {
            CLog::info("Room", "Create room {$request->name}");
            return redirect()->back()->with('success', __('Msg.RoomCreated'));
        }

        CLog::error("Room", "Could not create room {$request->name}");
        return redirect()->back()->withErrors(['message' => 'Room could not be created']);
    }

    public function update(UpdateRoomRequest $request)
    {
        if (Room::find($request->id)->update($request->except(['_token', '_method']))) {
            CLog::info("Room", "Update room {$request->name}");
            return redirect()->back()->with('success', __('Msg.RoomUpdated'));
        }

        CLog::error("Room", "Could not update room {$request->name}");
        return redirect()->back()->withErrors(['message' => 'Room could not be updated']);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:rooms,id',
        ])->validate();

        $find = Room::find($request->id);
        if ($find->delete()) {
            CLog::info("Room", "Delete room {$find->name}");
            return redirect()->back()->with('success', __('Msg.RoomDeleted'));
        }

        CLog::error("Room", "Could not delete room {$find->name}");
        return redirect()->back()->with('message', 'Room could not be deleted');
    }
}
