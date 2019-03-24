<?php

return [
    'cache-prefix' => '',

    'disabled' => env('MODEL_CACHE_DISABLED', false),

    'store' => env('MODEL_CACHE_STORE'),

    'enable-cooldown' => env('MODEL_CACHE_COOLDOWN_ENABLE', true),

    'cooldown-disable' => [
		//put full qualified classnames here
    ]
];
