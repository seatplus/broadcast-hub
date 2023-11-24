<?php

namespace Seatplus\BroadcastHub\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChannelsRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('manage broadcasts channel');
    }

    public function rules()
    {
        return [
            // id must be integer or string
            'checkedChannels.*.id' => ['required', function ($attribute, $value, $fail) {
                if (! is_int($value) && ! is_string($value)) {
                    $fail($attribute.' is not a valid id. It must be integer or string.');
                }
            }],
            'checkedChannels.*.name' => ['required', 'string'],
        ];
    }
}
