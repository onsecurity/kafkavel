<?php

namespace OnSecurity\Kafkavel\Resources\Discovery;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

abstract class Discover
{
    abstract protected static function getDirectories(): array;

    abstract protected static function getInterface(): string;

    abstract protected static function getConfigItems(): array;

    abstract protected static function isDiscoverEnabled(): bool;

    public static function discover(): Collection
    {
        $items = collect(static::getConfigItems());

        if (static::isDiscoverEnabled()) {
            $items = $items->merge(collect(static::getClassNames(
                (new Finder)->files()->in(static::getDirectories())
            ))->values());
        }

        return $items->unique();
    }

    protected static function getClassNames(\IteratorAggregate $files): Collection
    {
        $producerClasses = collect([]);

        foreach ($files as $file) {
            try {
                $producerClass = static::classFromFile($file, base_path());
                $producerReflection = new ReflectionClass($producerClass);
                if (!$producerReflection->isInstantiable()) {
                    continue;
                }
                if ($producerReflection->implementsInterface(static::getInterface())) {
                    $producerClasses[] = $producerClass;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return $producerClasses->filter();
    }

    /**
     * Pinched this from Illuminate\Foundation\Events\DiscoverEvents.
     * @param SplFileInfo $file
     * @param $basePath
     * @return string|string[]
     */
    protected static function classFromFile(SplFileInfo $file, $basePath)
    {
        $class = trim(Str::replaceFirst($basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);

        return str_replace(
            [DIRECTORY_SEPARATOR, ucfirst(basename(app()->path())) . '\\'],
            ['\\', app()->getNamespace()],
            ucfirst(Str::replaceLast('.php', '', $class))
        );
    }
}
