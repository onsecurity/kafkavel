<?php

namespace OnSecurity\Kafkavel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\Kafka\Config\Sasl;
use Junges\Kafka\Contracts\KafkaProducerMessage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use OnSecurity\Kafkavel\Auth\MskIamTokenProvider;
use OnSecurity\Kafkavel\Auth\OAuthBearerCallbackSetter;

class KafkaProduce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private string $topic, private KafkaProducerMessage $message)
    {

    }

    public function handle(): void
    {
        $messageProducer = Kafka::publishOn($this->topic)
            ->withMessage($this->message)
            ->withDebugEnabled(config('kafkavel.debug') ?? false);

        $mechanism = config('kafkavel.security.mechanism');

        if ($mechanism === 'AWS_MSK_IAM') {
            $provider = new MskIamTokenProvider(config('kafkavel.security.aws_region', 'eu-west-2'));
            $messageProducer->withConfigOptions([
                'security.protocol' => config('kafkavel.security.protocol', 'SASL_SSL'),
                'sasl.mechanisms' => 'OAUTHBEARER',
            ]);
            OAuthBearerCallbackSetter::set($messageProducer, $provider->getRefreshCallback());
        } elseif (config('kafkavel.security.username') !== null && config('kafkavel.security.password') !== null && $mechanism !== null) {
            $messageProducer->withSasl(new Sasl(
                username: config('kafkavel.security.username'),
                password: config('kafkavel.security.password'),
                mechanisms: $mechanism,
                securityProtocol: config('kafkavel.security.protocol')
            ));
        } else {
            $messageProducer->withConfigOptions(['security.protocol' => config('kafkavel.security.protocol')]);
        }

        $messageProducer->send();
    }
}
