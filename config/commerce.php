<?php

return [
    'tax' => [
        'default_rate' => env('TAX_RATE', 0.14),
    ],
    'shipping' => [
        'free_threshold' => 2000,
        'flat_rate' => 50,
    ],
];
