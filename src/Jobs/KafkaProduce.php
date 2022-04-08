<?php

namespace OnSecurity\Kafkavel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\Kafka\Config\Sasl;
use Junges\Kafka\Contracts\KafkaProducerMessage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

class KafkaProduce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private string $topic, private KafkaProducerMessage $message)
    {

    }

    public function handle(): void
    {
        $messageProducer = Kafka::publishOn($this->topic)
            ->withMessage($this->message);

        if (config('kafkavel.security.username') !== null && config('kafkavel.security.password') !== null && config('kafkavel.security.mechanism') !== null) {
            $messageProducer->withSasl(new Sasl(
                username: config('kafkavel.security.username'),
                password: config('kafkavel.security.password'),
                mechanisms: config('kafkavel.security.mechanism'),
                securityProtocol: config('kafkavel.security.protocol')
            ));
        } else {
            $messageProducer->withConfigOptions(['security.protocol' => config('kafkavel.security.protocol')]);
        }

        $messageProducer->send();
    }
}
