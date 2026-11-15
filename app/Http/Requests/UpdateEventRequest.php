<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

        if ($this->exists('level') && in_array($this->input('level'), ['', 'all'], true)) {
            $this->merge(['level' => null]);
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
            'custom_sport' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn () => $this->input('sport') === 'other'
                    && ! ($event instanceof Event && $event->hasUnspecifiedSport())),
            ],
            'event_date' => $polling
                ? ['exclude']
                : ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'max_participants' => ['nullable', 'integer', 'min:2'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', 'in:public,private'],
            'setting' => ['nullable', 'in:indoor,outdoor'],
            'level' => ['nullable', 'in:'.implode(',', array_keys(Event::LEVELS))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'setting.in' => 'Pick indoors or outdoors.',
            'level.in' => 'Pick all levels, beginner, intermediate, advanced, or pro.',
            'max_participants.min' => 'A game needs at least two spots.',
        ];
    }
}
