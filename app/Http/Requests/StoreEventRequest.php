<?php

namespace App\Http\Requests;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareSport();
    }

    protected function prepareSport(): void
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'sport' => ['required', 'in:'.implode(',', array_keys(Event::SPORTS))],
            'custom_sport' => ['nullable', 'required_if:sport,other', 'string', 'max:50'],
            'schedule_type' => ['nullable', 'in:fixed,poll'],
            'event_date' => ['exclude_if:schedule_type,poll', 'required', 'date', 'after:now'],
            'options' => ['exclude_unless:schedule_type,poll', 'array'],
            'options.*' => ['nullable', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'max_participants' => ['nullable', 'integer', 'min:2'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', 'in:public,private'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('schedule_type') !== 'poll') {
                return;
            }

            $options = collect($this->input('options', []))
                ->filter(fn ($value) => filled($value))
                ->map(function ($value) {
                    try {
                        return Carbon::parse($value);
                    } catch (\Throwable) {
                        return null;
                    }
                })
                ->filter()
                ->values();

            if ($options->count() < 2) {
                $validator->errors()->add('options', 'Add at least two times.');

                return;
            }

            $unique = $options->unique(fn (Carbon $date) => $date->format('Y-m-d H:i'));

            if ($unique->count() !== $options->count()) {
                $validator->errors()->add('options', 'Each time must be different.');
            }

            if ($options->contains(fn (Carbon $date) => $date->lessThanOrEqualTo(now()))) {
                $validator->errors()->add('options', 'Times must be in the future.');
            }
        });
    }
}
