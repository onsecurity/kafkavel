<?php

return [
    'enabled' => true,

    // producers are classes that implement \OnSecurity\Kafkavel\Resources\Contracts\KafkavelProducer.
    // They can be manually defined here or auto-discovery can be used by setting producer_discovery.enabled to true below
    'producers' => [
        /* Example
         * App\Producers\MyProducer::class
         */
    ],
    // Producer discovery uses a directory path to recursively search for classes that implement the \OnSecurity\Kafkavel\Resources\Contracts\KafkavelProducer interface.
    'producer_discovery' => [
        'enabled' => false,
        'directories' => ['app/Resources/Kafka/Producers'],
    ],

    // when producing messages, a job is pushed onto a queue, this config options defines the queue that is used
    'producer_queue' => env('KAFKAVEL_QUEUE', env('QUEUE_DRIVER', 'sync')),

    // consumers are classes that implement \OnSecurity\Kafkavel\Resources\Contracts\KafkavelConsumers.
    // They can be manually defined here or auto-discovery can be used by setting consumer_discovery.enabled to true below
    'consumers' => [
        /* Example
         * App\Consumers\MyConsumer::class
         */
    ],
    'consumer_discovery' => [
        'enabled' => false,
        'directories' => ['app/Resources/Kafka/Consumers']
    ],
    'security' => [
        'username' => env('KAFKAVEL_SECURITY_USERNAME'),
        'password' => env('KAFKAVEL_SECURITY_PASSWORD'),
        'mechanism' => env('KAFKAVEL_SECURITY_MECHANISM'),
        'protocol' => env('KAFKAVEL_SECURITY_PROTOCOL', 'plaintext')
    ]
];
