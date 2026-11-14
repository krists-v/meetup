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

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sport') && $this->filled('sport_type')) {
            $this->merge(['sport' => $this->input('sport_type')]);
        }

        if ($this->input('sport') === 'other') {
            $custom = trim((string) $this->input('custom_sport', ''));
            $this->merge(['custom_sport' => $custom === '' ? null : $custom]);
        } else {
            $this->merge(['custom_sport' => null]);
        }
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
            'sport' => ['required', 'in:'.implode(',', array_keys(Event::SPORTS))],
            'custom_sport' => ['nullable', 'required_if:sport,other', 'string', 'max:50'],
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
