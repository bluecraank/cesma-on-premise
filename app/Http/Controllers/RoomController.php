<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Helper\CLog;
use App\Http\Requests\StoreRoomRequest;

class RoomController extends Controller
{
    public function store(StoreRoomRequest $request)
    {
        if (Room::create($request->except('_token', '_method'))) {
            CLog::info("Room", "Room created", null,  $request->name);
            return redirect()->back()->with('success', __('Room successfully created'));
        }

        return redirect()->back()->withErrors(['message' => 'Room could not be created']);
    }
}
