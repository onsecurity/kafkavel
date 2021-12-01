<?php

namespace OnSecurity\Kafkavel\Resources\Consumers;

use Illuminate\Support\Collection;
use OnSecurity\Kafkavel\Resources\Discovery\ConsumerDiscover;

class ConsumerMap
{
    protected Collection $consumerClasses;
    protected array $topicConsumerSchemaMap;

    public function __construct(?array $consumerClasses = null)
    {
        $this->consumerClasses = $consumerClasses !== null ? collect($consumerClasses) : ConsumerDiscover::discover();
        $this->topicConsumerSchemaMap = $this->consumersToTopicSchemaMap($this->consumerClasses);
    }

    public function getTopics(): Collection
    {
        return $this->consumerClasses->map(fn($consumerClass) => $consumerClass::getTopic())->uniqueStrict()->values();
    }

    public function getTopicSchemaMap(): array
    {
        return $this->topicConsumerSchemaMap;
    }

    protected function consumersToTopicSchemaMap(Collection $consumers): array
    {
        return collect($consumers)
            ->groupBy(fn($consumerClass) => $consumerClass::getTopic())
            ->map(fn($consumerClasses) =>
            $consumerClasses->mapWithKeys(fn($consumerClass) => [
                $consumerClass => [
                    $consumerClass::getSchema() => (is_array($consumerClass::getSchemaVersion()) ? $consumerClass::getSchemaVersion() : [$consumerClass::getSchemaVersion()])
                ]
            ])->groupBy(fn($item) => collect($item)->keys()->first(), true)
                ->map(fn($item) => $item->map(fn($i) => reset($i)))
                ->map(fn($item) => $item->groupBy(fn($i) => $i, true))
                ->map(fn($item) => $item->map(fn($i) => $i->keys()->toArray()))
                ->toArray()
            )->toArray();
    }
}
