<?php

return [
    // Enter your client id and client secret from Tokenly Music here
    'client_id'     => env('TOKENLY_MUSIC_CLIENT_ID',      'YOUR_TOKENLY_MUSIC_CLIENT_ID_HERE'),
    'client_secret' => env('TOKENLY_MUSIC_CLIENT_SECRET',  'YOUR_TOKENLY_MUSIC_CLIENT_SECRET_HERE'),

    // this is the Tokenly Music API URL
    'base_url'      => env('TOKENLY_MUSIC_BASE_URL', 'https://music.tokenly.com/api/v1'),
];

