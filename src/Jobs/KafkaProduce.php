<?php

namespace OnSecurity\Kafkavel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
        Kafka::publishOn($this->topic)
            ->withMessage($this->message)
            ->send();
    }
}
