<?php

namespace OnSecurity\Kafkavel\Resources\Topic;

use Illuminate\Support\Collection;

class Rewriter
{
    private static ?array $rewriteMapCache = null;

    public static function clearRewriteMapCache(): void
    {
        self::$rewriteMapCache = null;
    }

    public static function getRewriteMap(bool $reloadCache = false): array
    {
        if ((self::$rewriteMapCache === null || $reloadCache) && strlen(config('kafkavel.topic_rewrite'))) {
            $rewriteConfig = array_map('trim',  explode(',', config('kafkavel.topic_rewrite')));
            $rewriteConfigItems = array_map(fn($item) => array_map('trim', explode('->', $item)), $rewriteConfig);
            foreach ($rewriteConfigItems as $rewriteConfigItem) {
                if (count($rewriteConfigItem) !== 2) {
                    throw new \Exception('Invalid rewrite config item, expected length of 2, got ' . count($rewriteConfigItem) . ' (' . json_encode($rewriteConfigItem) . ')');
                }
                self::$rewriteMapCache[$rewriteConfigItem[0]] = $rewriteConfigItem[1];
            }
        }
        return self::$rewriteMapCache ?? [];
    }

    public static function collectionMap(Collection $topics, $reloadCache = false): Collection
    {
        if ($reloadCache) {
            self::getRewriteMap($reloadCache);
        }
        return $topics->map(fn(string $topic) => self::map($topic));
    }

    public static function collectionReverseMap(Collection $rewrittenTopics, $reloadCache = false): Collection
    {
        if ($reloadCache) {
            self::getRewriteMap($reloadCache);
        }
        return $rewrittenTopics->map(fn(string $topic) => self::reverseMap($topic));
    }

    public static function arrayMap(array $topics, bool $reloadCache = false): array
    {
        if ($reloadCache) {
            self::getRewriteMap($reloadCache);
        }
        return array_map(fn(string $topic) => self::map($topic), $topics);
    }

    public static function arrayReverseMap(array $rewrittenTopics, bool $reloadCache = false): array
    {
        if ($reloadCache) {
            self::getRewriteMap($reloadCache);
        }
        return array_map(fn(string $topic) => self::reverseMap($topic), $rewrittenTopics);
    }

    public static function map(string $topic, bool $reloadCache = false): string
    {
        return self::getRewriteMap($reloadCache)[$topic] ?? $topic;
    }

    public static function reverseMap(string $rewrittenTopic, bool $reloadCache = false): string
    {
        return array_flip(self::getRewriteMap($reloadCache))[$rewrittenTopic] ?? $rewrittenTopic;

    }
}