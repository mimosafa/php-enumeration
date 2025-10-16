<?php

declare(strict_types=1);

namespace Enumeration;

use LogicException;
use ReflectionClass;

/**
 * A trait that automatically enumerates class constants to be used as enum cases.
 *
 * By using this trait, you can define your enum cases as simple class constants,
 * and they will be automatically available through the `toArray()` method.
 */
trait EnumerateConstantsTrait
{
    /**
     * Cache for the enumerated constants.
     *
     * @var array<class-string, array<string, mixed>>
     */
    protected static array $enumeratedConstants = [];

    /**
     * Returns the enumerated constants as an array.
     *
     * This method caches the result for performance.
     *
     * @return array<string, mixed>
     */
    public static function toArray(): array
    {
        $class = get_called_class();
        return self::$enumeratedConstants[$class] ??= static::expandConstants();
    }

    /**
     * Expands and filters the class constants using reflection.
     *
     * This method applies inclusion/exclusion logic and checks for duplicates.
     *
     * @return array<string, mixed>
     */
    protected static function expandConstants(): array
    {
        $constants = (new ReflectionClass(static::class))->getConstants();

        $included = static::includedConstantsFromEnumeration();
        $excluded = static::excludedConstantsFromEnumeration();
        $duplicatable = static::allowDuplicateValues();

        $filter = function ($value, string $key) use ($included, $excluded, $duplicatable): bool
            {
                static $cache = [];

                if (! empty($included)) {
                    if (! in_array($key, $included, true)) {
                        return false;
                    }
                } else if (! empty($excluded)) {
                    if (in_array($key, $excluded, true)) {
                        return false;
                    }
                }
                if (! $duplicatable) {
                    if (in_array($value, $cache, true)) {
                        throw new LogicException("Duplicate value found in enum constants: " . (string) $value);
                    }
                    $cache[] = $value;
                }

                return static::validateConstantValue($value);
            }
        ;

        return array_filter($constants, $filter, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Template method to specify which constants to include.
     *
     * If this method returns a non-empty array, only the constants with names
     * in this array will be included in the enumeration.
     *
     * @return string[] An array of constant names to include.
     */
    protected static function includedConstantsFromEnumeration(): array
    {
        return [];
    }

    /**
     * Template method to specify which constants to exclude.
     *
     * This method is ignored if `includedConstantsFromEnumeration` returns a non-empty array.
     *
     * @return string[] An array of constant names to exclude.
     */
    protected static function excludedConstantsFromEnumeration(): array
    {
        return [];
    }

    /**
     * Template method to determine if duplicate values are allowed.
     *
     * @return bool True if duplicate values are allowed, false otherwise.
     */
    protected static function allowDuplicateValues(): bool
    {
        return true;
    }

    /**
     * Template method to validate a constant value.
     *
     * @param mixed $value The value of the constant.
     * @return bool True if the value is valid, false otherwise.
     */
    protected static function validateConstantValue(mixed $value): bool
    {
        return true;
    }
}
