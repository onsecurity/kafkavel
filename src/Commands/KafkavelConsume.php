<?php

namespace OnSecurity\Kafkavel\Commands;

use Illuminate\Console\Command;
use Junges\Kafka\Exceptions\KafkaConsumerException;
use OnSecurity\Kafkavel\Resources\Consumers\ConsumeManager;

class KafkavelConsume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafkavel:consume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the consume process for OnSecurity Kafkavel';

    private ?ConsumeManager $consumeManager = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        declare(ticks=1);
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, fn() => $this->handleSigInt());
        pcntl_signal(SIGTERM, fn() => $this->handleSigInt());

        $this->info('Consume Manager Starting');

        $this->startConsumer();
    }

    private function startConsumer(): void
    {
        try {
            $this->consumeManager = new ConsumeManager;
            $this->consumeManager->start();
        } catch (KafkaConsumerException $e) {
            $this->consumeManager = null;
            $this->error('Failed to start Consume Manager: ' . $e->getMessage() . '. Trying again in 30 seconds...');
            sleep(30);
            $this->startConsumer();
        }
    }

    private function handleSigInt()
    {
        $this->info('Consume Manager gracefully shutting down...');
        if ($this->consumeManager !== null) {
            $this->consumeManager->stop();
        }
        $this->info('Consume Manager Shutdown');
        exit(0);
    }
}
