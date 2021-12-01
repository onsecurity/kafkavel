<?php

namespace OnSecurity\Kafkavel\Resources\Aggregate;

use Illuminate\Support\Arr;

class DtoAssembler
{
    private array $data = [];

    public function __construct(private DataAggregate $aggregate)
    {

    }

    /**
     * @param string $key
     * @param $data
     * @return $this
     */
    public function addData(string $key, $data): self
    {
        $this->data[$key] = $data;
        return $this;
    }

    public function toArray(): ?array
    {
        return $this->aggregateData($this->aggregate, $this->data);
    }

    private function aggregateData(DataAggregate $aggregate, ?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        return collect($aggregate->getFields())->mapWithKeys(
            fn(DataAggregateField $field) => [$field->getTarget() => $this->getFieldValue($field, $data)]
        )->toArray();
    }

    private function getFieldValue(DataAggregateField $field, ?array $data)
    {
        $fieldData = $field->getSource() === '' ? $data : Arr::get($data, $field->getSource());
        if ($field->isLoop()) {
            return collect($fieldData)->map(fn($item) => $field->isAggregate() ? $this->aggregateData($field->getAggregate(), $item) : $item);
        }
        return $field->isAggregate() ? $this->aggregateData($field->getAggregate(), $fieldData) : $fieldData;
    }
}
