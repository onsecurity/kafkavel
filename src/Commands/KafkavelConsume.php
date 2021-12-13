<?php

namespace OnSecurity\Kafkavel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Exceptions\KafkaConsumerException;
use OnSecurity\Kafkavel\Resources\Consumers\ConsumeManager;
use OnSecurity\Kafkavel\Resources\Consumers\ConsumerMessage;

class KafkavelConsume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafkavel:consume {--D|debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the consume process for OnSecurity Kafkavel';

    private ?ConsumeManager $consumeManager = null;
    private bool $debug = false;

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

        $this->debug = $this->option('debug');

        if ($this->debug) {
            $this->info('Consume Manager Debug Mode Enabled - Watch your log output');
        }

        $this->info('Consume Manager Starting');

        $this->startConsumer();
    }

    private function startConsumer(): void
    {
        try {
            $this->consumeManager = new ConsumeManager;
            if ($this->debug) {
                $this->info('Subscribing to topics: ' . implode(', ', $this->consumeManager->getTopics()));
            }
            $this->consumeManager->start();
        } catch (KafkaConsumerException $e) {
            $this->consumeManager = null;
            $this->error('Failed to start Consume Manager: ' . $e->getMessage() . '. Trying again in 30 seconds...');
            sleep(30);
            $this->startConsumer();
        }
    }

    private function createConsumeManager(): void
    {
        $this->consumeManager = new ConsumeManager;
        if ($this->debug) {
            $this->consumeManager->beforeHandle(function(ConsumerMessage $consumerMessage, array $handlerClasses) {
                echo "Test\n";
                Log::debug('----------');
                Log::debug(sprintf(
                    '%s -> %s:%s -> %s',
                    $consumerMessage->getTopicName(),
                    $consumerMessage->getSchema(),
                    $consumerMessage->getSchemaVersion(),
                    implode(', ', $handlerClasses)
                ));
                Log::debug(json_encode($consumerMessage->getBody()));

                return true;
            });

            $this->consumeManager->afterHandle(function(ConsumerMessage $consumerMessage, array $handlerClasses, array $results) {
                Log::debug(json_encode($results));
            });
        }
    }

    private function handleSigInt()
    {
        $this->info('Consume Manager gracefully shutting down...');
        if ($this->consumeManager !== null) {
            $this->consumeManager->stop();
        }

        $handledMessages = $this->consumeManager->getHandledMessages();
        $ignoredMessages = $this->consumeManager->getIgnoredMessages();
        $totalMessages = $handledMessages + $ignoredMessages;
        $handledPercentage = $totalMessages === 0 ? 100 : round(($handledMessages / $totalMessages) * 100, 2);

        $this->comment(sprintf('Handled %d Ignored %d Handled Percentage %d%%',
            $handledMessages,
            $ignoredMessages,
            $handledPercentage
        ));

        $this->info('Consume Manager Shutdown');
        exit(0);
    }
}
