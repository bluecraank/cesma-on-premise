<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVlanRequest extends FormRequest
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
            'vid' => 'required|integer|between:1,4096',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'ip_range' => 'nullable|string',
            'scan' => 'nullable|string',
            'sync' => 'nullable|string',
            'site_id' => 'required|integer|exists:sites,id'
        ];
    }
}
