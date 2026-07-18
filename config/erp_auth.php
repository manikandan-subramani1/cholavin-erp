<?php

return [
    'throttle' => [
        'max_attempts' => (int) env('ERP_AUTH_MAX_ATTEMPTS', 5),
        'decay_seconds' => (int) env('ERP_AUTH_DECAY_SECONDS', 60),
    ],

    'captcha' => [
        'enabled' => (bool) env('ERP_AUTH_CAPTCHA_ENABLED', false),
    ],

    'challenges' => [
        'otp_enabled' => (bool) env('ERP_AUTH_OTP_ENABLED', false),
        'two_factor_enabled' => (bool) env('ERP_AUTH_TWO_FACTOR_ENABLED', false),
        'device_approval_enabled' => (bool) env('ERP_AUTH_DEVICE_APPROVAL_ENABLED', false),
    ],
];
