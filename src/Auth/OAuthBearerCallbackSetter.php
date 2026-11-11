<?php

namespace OnSecurity\Kafkavel\Auth;

/**
 * Adds the OAUTHBEARER token refresh callback to a laravel-kafka builder.
 *
 * laravel-kafka v1.x's InteractsWithConfigCallbacks trait doesn't include
 * withOAuthBearerTokenRefreshCallback() (added in v2.11.1, which requires
 * Laravel 12+). The $callbacks property is protected, so external code
 * can't set it directly.
 *
 * This class uses Closure::bind to access the protected property from
 * outside the class hierarchy — cleaner than full ReflectionProperty
 * usage and makes the intent explicit.
 *
 * When kafkavel upgrades to laravel-kafka v2.11+, replace usages of this
 * class with the native $builder->withOAuthBearerTokenRefreshCallback().
 */
class OAuthBearerCallbackSetter
{
    /**
     * Set the OAUTHBEARER token refresh callback on a builder.
     *
     * Works with both ProducerBuilder and ConsumerBuilder from laravel-kafka,
     * as both use the InteractsWithConfigCallbacks trait which declares
     * `protected array $callbacks`.
     */
    public static function set(object $builder, callable $callback): void
    {
        // Bind a closure to the builder's scope to access its protected $callbacks.
        $setter = \Closure::bind(function () use ($callback) {
            $this->callbacks['setOauthbearerTokenRefreshCb'] = $callback;
        }, $builder, $builder::class);

        $setter();
    }
}
