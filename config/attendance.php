<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default WiFi Enforcement
    |--------------------------------------------------------------------------
    |
    | When enabled globally or on a session, only requests originating from
    | the school's local WiFi subnet will be accepted for check-in.
    |
    */
    'default_require_wifi' => env('ATTENDANCE_REQUIRE_WIFI', false),

    /*
    |--------------------------------------------------------------------------
    | School WiFi Subnets & IP Ranges
    |--------------------------------------------------------------------------
    |
    | List of CIDR subnets or IP addresses belonging to the school campus network.
    | Any check-in from external mobile carriers (4G/5G) or residential home routers
    | will be rejected if WiFi enforcement is active.
    |
    */
    'school_subnets' => array_filter(array_map('trim', explode(',', env(
        'SCHOOL_WIFI_SUBNETS',
        '192.168.0.0/16,10.0.0.0/8,172.16.0.0/12,127.0.0.1,::1'
    )))),
];
