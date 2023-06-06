<?php

return [
    'autoload' => false,
    'hooks' => [
        'sms_send' => [
            'alisms',
        ],
        'sms_notice' => [
            'alisms',
        ],
        'sms_check' => [
            'alisms',
        ],
        'app_init' => [
            'crontab',
            'epay',
        ],
        'config_init' => [
            'nkeditor',
        ],
    ],
    'route' => [],
    'priority' => [],
];
