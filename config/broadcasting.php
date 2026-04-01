<?php

return [
    'default' => env('BROADCAST_CONNECTION', 'null'),
    'connections' => [
        'null' => [
            'driver' => 'null',
        ],
        'socket.io' => [
            'driver' => 'socket.io',
            'host' => env('SOCKET_IO_HOST', 'https://flexbiz.nodejs.tk'),
        ],
    ],
];
