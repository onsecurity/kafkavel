<?php

namespace OnSecurity\Kafkavel\Resources\Producers;

use Junges\Kafka\Message\Message;
use OnSecurity\Kafkavel\Resources\Aggregate\DtoAssembler;
use OnSecurity\Kafkavel\Jobs\KafkaProduce;
use OnSecurity\Kafkavel\Resources\Contracts\KafkavelProducer;
use OnSecurity\Kafkavel\Resources\Topic\Rewriter;

abstract class Producer implements KafkavelProducer
{
    protected array $headers = [];

    public function __construct(private object $event)
    {
        
    }

    final public function getEvent(): object
    {
        return $this->event;
    }

    public function shouldProduce(): bool
    {
        return true;
    }

    final public function produce(): void
    {
        dispatch(new KafkaProduce(Rewriter::map(static::getTopic()), $this->getMessage()))->onQueue(config('kafkavel.producer_queue'));
    }

    public function getMessage() {
        return new Message(
            topicName: Rewriter::map($this->getTopic()),
            headers: array_merge($this->headers, ['schema' => static::getSchema(), 'schemaVersion' => static::getSchemaVersion()]),
            body: $this->getAssembler()->toArray()
        );
    }

    abstract static public function getEventClass(): string | array;

    abstract static public function getSchema(): string;

    abstract static public function getSchemaVersion(): string|int;

    abstract static public function getTopic(): string;

    abstract public function getAssembler(): DtoAssembler;
}
