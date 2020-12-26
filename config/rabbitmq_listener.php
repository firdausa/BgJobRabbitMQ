<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Events config
    |--------------------------------------------------------------------------
    |
    | Here you can determine all events you can receive.
    | The key of array element is event name and value is a class which is responsible for
    | handling of this event
    |
    | Example:
    |
    |   'events' => [
    |       'product.updated' => 'App\EventHandlers\ProductUpdater'
    |   ]
    |
    */
    'events' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored events config
    |--------------------------------------------------------------------------
    |
    | Here you can determine which events you wish to ignore.
    |
    | Example:
    |
    |   'ignored_events' => [
    |       'product.updated',
    |       'product.added'
    |   ]
    |
    */
    'ignored_events' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Queues
    |--------------------------------------------------------------------------
    |
    | Here you can determine which queues you wish to listen.
    |
    | Example:
    |
    |   'queues' => [
    |       'my-site.products',
    |   ]
    |
    */
    'queues' => [
        ''
    ],

    /*
    |--------------------------------------------------------------------------
    | Events acknowledgement
    |--------------------------------------------------------------------------
    |
    | Determines if you will acknowledge events when you successfully process them.
    |
    */

    'acknowledge_events' => true,
    /*
    |--------------------------------------------------------------------------
    | Events rejecting
    |--------------------------------------------------------------------------
    |
    | Determines if you will reject events when you can't process them.
    |
    */
    'reject_events' => true,

    /*
    |--------------------------------------------------------------------------
    | RabbitMQ host
    |--------------------------------------------------------------------------
    */
    'host' => env('RABBITMQ_LISTENER_HOST', 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | RabbitMQ port
    |--------------------------------------------------------------------------
    */
    'port' => env('RABBITMQ_LISTENER_PORT', '5672'),

    /*
    |--------------------------------------------------------------------------
    | RabbitMQ user
    |--------------------------------------------------------------------------
    */
    'user' => env('RABBITMQ_LISTENER_USER', 'guest'),

    /*
    |--------------------------------------------------------------------------
    | RabbitMQ password
    |--------------------------------------------------------------------------
    */
    'password' => env('RABBITMQ_LISTENER_PASSWORD', 'guest'),

    /*
    |--------------------------------------------------------------------------
    | RabbitMQ vhost
    |--------------------------------------------------------------------------
    */
    'vhost' => env('RABBITMQ_LISTENER_VHOST', '/'),

];
