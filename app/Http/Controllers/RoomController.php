<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'building_id' => 'required|integer|exists:buildings,id',
        ])->validate();

        if (Room::create($request->except('_token', '_method'))) {
            // LogController::log('Raum erstellt', '{"name": "' . $request->name . '", "location_id": "' . $request->location_id . '"}');
            return redirect()->back()->with('success', __('Msg.RoomCreated'));
        }

        return redirect()->back()->withErrors(['message' => 'Room could not be created']);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'id' => 'required|integer|exists:rooms,id',
            'building_id' => 'required|integer|exists:buildings,id',
        ])->validate();

        if (Room::find($request->id)->update($request->except(['_token', '_method']))) {
            // LogController::log('Raum aktualisiert', '{"name": "' . $request->name . '"}');

            return redirect()->back()->with('success', __('Msg.RoomUpdated'));
        }
        return redirect()->back()->withErrors(['message' => 'Room could not be updated']);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:rooms,id',
        ])->validate();

        $find = Room::find($request->id);
        if ($find->delete()) {
            // LogController::log('Raum gelöscht', '{"name": "' . $find->name . '"}');

            return redirect()->back()->with('success', __('Msg.RoomDeleted'));
        }
        return redirect()->back()->with('message', 'Room could not be deleted');
    }
}
