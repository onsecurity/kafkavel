<?php

namespace OnSecurity\Kafkavel\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Exceptions\KafkaConsumerException;
use OnSecurity\Kafkavel\Exceptions\ConsumerManagerStartException;
use OnSecurity\Kafkavel\Resources\Consumers\ConsumeManager;
use OnSecurity\Kafkavel\Resources\Consumers\ConsumerMessage;

class KafkavelConsume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafkavel:consume {--D|debug} {--T|topic=*}';

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
        pcntl_signal(SIGQUIT, fn() => $this->handleSigInt());

        $this->debug = $this->option('debug');

        if ($this->debug) {
            $this->info('Consume Manager Debug Mode Enabled - Watch your log output');
        }

        $this->info('Consume Manager Starting');

        return $this->startConsumer();
    }

    private function startConsumer(): int
    {
        try {
            $this->consumeManager = $this->createConsumeManager();
            $this->consumeManager->start();
            return 0;
        } catch (ConsumerManagerStartException $e) {
            $this->error('Failed to start Consume Manager: ' . $e->getMessage());
            return 1;
        } catch (KafkaConsumerException $e) {
            $this->error('Failed to start Consume Manager: ' . $e->getMessage());
            return 1;
        } catch (Exception $e) {
            $this->error('Unknown error occurred: ' . $e->getMessage());
            return 1;
        }
    }

    private function createConsumeManager(): ConsumeManager
    {
        $topicFilter = $this->option('topic');
        $consumeManager = new ConsumeManager(empty($topicFilter) ? null : $topicFilter);
        if ($this->debug) {
            $this->info('Subscribing to topics: ' . implode(', ', $consumeManager->getTopics()));
            $consumeManager->beforeHandle(function(ConsumerMessage $consumerMessage, array $handlerClasses) {
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

            $consumeManager->afterHandle(function(ConsumerMessage $consumerMessage, array $handlerClasses, array $results) {
                Log::debug(json_encode($results));
            });
        }

        return $consumeManager;
    }

    private function handleSigInt()
    {
        $this->info('Consume Manager gracefully shutting down...');
        if ($this->consumeManager !== null) {
            $this->consumeManager->stop();

            $handledMessages = $this->consumeManager->getHandledMessages();
            $ignoredMessages = $this->consumeManager->getIgnoredMessages();
            $totalMessages = $handledMessages + $ignoredMessages;
            $handledPercentage = $totalMessages === 0 ? 100 : round(($handledMessages / $totalMessages) * 100, 2);

            $this->comment(sprintf('Handled %d Ignored %d Handled Percentage %d%%',
                $handledMessages,
                $ignoredMessages,
                $handledPercentage
            ));
        }

        $this->info('Consume Manager Shutdown');
        exit(0);
    }
}
