<?php

namespace Seatplus\BroadcastHub\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBroadcastRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => 'required|string',
            'enabled' => 'boolean',
        ];
    }
}
