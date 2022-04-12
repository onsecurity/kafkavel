<?php

namespace OnSecurity\Kafkavel\Resources\Producers;

use Illuminate\Support\Collection;
use OnSecurity\Kafkavel\Resources\Discovery\ProducerDiscover;

final class ProducerEventHandler
{
    protected object $event;
    private Collection $producers;

    public function __construct(object $event)
    {
        $this->event = $event;
        $this->producers = $this->getProducers();
    }

    public function handle(): void
    {
        if ($this->producers->isNotEmpty() && config('kafkavel.enabled')) {
            foreach ($this->producers as $producer) {
                if ($producer->shouldProduce()) {
                    $producer->produce();
                }
            }
        }
    }

    private function getProducers()
    {
        $producers = ProducerDiscover::discover();

        return $producers->filter(function($producerClass) {
            $eventClass = $producerClass::getEventClass();
            return is_array($eventClass) ? in_array(get_class($this->event), $eventClass, true) : $eventClass === get_class($this->event);
        })->map(fn($producerClass) => app()->make($producerClass, ['event' => $this->event]));
    }
}
