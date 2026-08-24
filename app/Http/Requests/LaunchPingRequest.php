<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaunchPingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'duration_sec' => 'nullable|integer|min:1|max:60',
            'timeout' => 'nullable|integer|min:1|max:10',
        ];
    }
}
