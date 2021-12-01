<?php

namespace OnSecurity\Kafkavel\Resources\Contracts;

use OnSecurity\Kafkavel\Resources\Aggregate\DtoAssembler;

interface KafkavelProducer {
    public function __construct(object $event);

    static public function getEventClass(): string | array;

    static public function getSchema(): string;

    static public function getSchemaVersion(): string|int;

    static public function getTopic(): string;

    public function shouldProduce(): bool;

    public function produce(): void;

    public function getEvent(): object;

    public function getAssembler(): DtoAssembler;
}
