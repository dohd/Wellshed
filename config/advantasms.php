<?php

return [
    'endpoint'   => env('ADVANTA_API_URL', 'https://quicksms.advantasms.com/api/services/sendsms/'),
    'apikey'     => env('ADVANTA_API_KEY'),
    'partner_id' => env('ADVANTA_PARTNER_ID'),
    'shortcode'  => env('ADVANTA_SHORTCODE', 'GRANGE PARK'),
];
