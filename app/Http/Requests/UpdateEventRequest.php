<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event !== null && $this->user()->can('update', $event);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $event = $this->route('event');
        $polling = $event instanceof Event && $event->isPoll();

        return [
            'title' => ['required', 'string', 'max:255'],
            'sport_type' => ['required', 'in:football,tennis,basketball,other'],
            'event_date' => $polling
                ? ['exclude']
                : ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'max_participants' => ['nullable', 'integer', 'min:2'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', 'in:public,private'],
        ];
    }
}
