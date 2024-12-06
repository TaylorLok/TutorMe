<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.required' => 'Please select a user to call',
            'receiver_id.exists' => 'Selected user is invalid',
            'scheduled_at.required' => 'Schedule time is required',
            'scheduled_at.date' => 'Invalid date format',
            'scheduled_at.after' => 'Schedule time must be in the future',
        ];
    }
}
