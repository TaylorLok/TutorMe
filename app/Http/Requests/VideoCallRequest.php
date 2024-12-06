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
            'tutor_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'tutor_id.required' => 'A tutor is required',
            'tutor_id.exists' => 'Selected tutor is invalid',
            'student_id.required' => 'A student is required',
            'student_id.exists' => 'Selected student is invalid',
            'scheduled_at.required' => 'Schedule time is required',
            'scheduled_at.date' => 'Invalid date format',
            'scheduled_at.after' => 'Schedule time must be in the future',
        ];
    }
}
