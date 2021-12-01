<?php

namespace OnSecurity\Kafkavel\Test\Support;

class TestProducerEvent
{
    public function __construct(public bool $shouldProduce = true)
    {

    }

    public function data(): array
    {
        return [
            'foo' => 'bar',
            'hello' => 'world'
        ];
    }
}
