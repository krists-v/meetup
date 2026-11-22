<?php

return [

    'confirmed' => 'Those do not match. Type the :attribute again.',
    'current_password' => 'That password doesn\'t match. Try again.',
    'date' => 'That date does not look right.',
    'email' => 'That does not look like an email. Check it and try again.',
    'image' => 'Use a photo.',
    'in' => 'Pick one of the options.',
    'max' => [
        'file' => 'That file is too big. Keep it under :max kilobytes.',
        'numeric' => 'That :attribute is too high.',
        'string' => 'Keep the :attribute under :max characters.',
    ],
    'mimes' => 'Use a jpg, png, webp, or gif.',
    'min' => [
        'numeric' => 'Use at least :min.',
        'string' => 'Use at least :min characters.',
    ],
    'required' => 'Add your :attribute.',
    'unique' => 'That :attribute is already taken. Try another.',

    'password' => [
        'letters' => 'Use at least one letter.',
        'mixed' => 'Use both upper and lower case letters.',
        'numbers' => 'Use at least one number.',
        'symbols' => 'Use at least one symbol.',
        'uncompromised' => 'That password has shown up in a data leak. Pick another.',
    ],

    'custom' => [
        'max_participants' => [
            'min' => 'A game needs at least two spots.',
        ],
    ],

    'attributes' => [
        'avatar' => 'photo',
        'body' => 'note',
        'current_password' => 'current password',
        'custom_sport' => 'activity',
        'description' => 'note',
        'email' => 'email',
        'event_date' => 'date and time',
        'favorite_sports' => 'sports',
        'level' => 'level',
        'sport_levels' => 'level',
        'sport_levels.*' => 'level',
        'location' => 'place',
        'max_participants' => 'number of spots',
        'name' => 'name',
        'options' => 'times',
        'password' => 'password',
        'setting' => 'indoors or outdoors',
        'sport' => 'activity',
        'title' => 'name',
        'visibility' => 'who can see it',
    ],

];
