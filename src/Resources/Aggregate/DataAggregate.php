<?php

namespace OnSecurity\Kafkavel\Resources\Aggregate;

class DataAggregate
{
    protected array $fields = [];

    public function createAndAddField(string $source, string $target): self
    {
        $aggregateField = new DataAggregateField($source, $target);
        $this->addField($aggregateField);
        return $this;
    }

    public function addField(DataAggregateField $aggregateField): self
    {
        $this->fields[] = $aggregateField;
        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}
