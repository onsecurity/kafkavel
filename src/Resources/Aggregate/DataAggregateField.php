<?php

namespace OnSecurity\Kafkavel\Resources\Aggregate;

class DataAggregateField
{
    private bool $loop = false;
    private ?DataAggregate $aggregate = null;

    public function __construct(private string $source, private string $target)
    {

    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function isAggregate(): bool
    {
        return $this->aggregate !== null;
    }

    public function getAggregate(): DataAggregate
    {
        if (!$this->isAggregate()) {
            throw new \Exception('Unable to get aggregate for non-aggregate ' . static::class);
        }

        return $this->aggregate;
    }

    public function setAggregate(DataAggregate $aggregate): self
    {
        $this->aggregate = $aggregate;
        return $this;
    }

    public function setLoop(bool $loop = true): self
    {
        $this->loop = $loop;
        return $this;
    }

    public function isLoop(): bool
    {
        return $this->loop;
    }
}
