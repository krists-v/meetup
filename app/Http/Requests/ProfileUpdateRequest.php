<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('favorite_sports')) {
            $this->merge(['favorite_sports' => []]);
        }

        if (is_array($this->input('sport_levels'))) {
            $levels = [];

            foreach ($this->input('sport_levels') as $sport => $level) {
                $levels[$sport] = $level === '' ? null : $level;
            }

            $this->merge(['sport_levels' => $levels]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $levels = $this->input('sport_levels', []);

            if (! is_array($levels)) {
                return;
            }

            foreach (array_keys($levels) as $sport) {
                if (! is_string($sport) || ! array_key_exists($sport, Event::SPORTS)) {
                    $validator->errors()->add('sport_levels', 'Pick a sport you play.');

                    break;
                }
            }
        });
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'remove_avatar' => ['sometimes', 'boolean'],
            'favorite_sports' => ['array'],
            'favorite_sports.*' => ['in:'.implode(',', array_keys(Event::SPORTS))],
            'sport_levels' => ['sometimes', 'array'],
            'sport_levels.*' => ['nullable', Rule::in(array_keys(Event::LEVELS))],
            'game_reminders' => ['sometimes', 'boolean'],
        ];
    }
}
