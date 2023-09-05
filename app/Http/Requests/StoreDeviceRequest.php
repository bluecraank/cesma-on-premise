<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:devices|max:100',
            'hostname' => 'required|unique:devices|max:100',
            'building_id' => 'required|integer|exists:buildings,id',
            'site_id' => 'required|integer|exists:sites,id',
            'room_id' => 'required|integer|exists:rooms,id',
            'location_description' => 'required|integer',
            'type' => 'required|string'
        ];
    }
}
