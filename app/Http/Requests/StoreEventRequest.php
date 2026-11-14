<?php

namespace App\Http\Requests;

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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'sport_type' => ['required', 'in:football,tennis,basketball,other'],
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
