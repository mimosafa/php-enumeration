<?php

declare(strict_types=1);

namespace Enumeration;

use Error;
use LogicException;
use ValueError;

/**
 * A base class for creating inheritable, pure enumerations.
 *
 * For IDE autocompletion with magic static calls, add @method annotations to
 * the child class PHPDoc.
 *
 * Example child class annotations:
 * @method static self PENDING()
 * @method static self PUBLISHED()
 */
abstract class PureEnum
{
    /**
     * Cache of enumeration cases per class.
     *
     * @var array<class-string<static>, array<string, static>>
     */
    protected static $wholeCases = [];

    /**
     * Provides the case-sensitive names on each case.
     *
     * @return string[]
     */
    abstract public static function toArray(): array;

    /**
     * Protected constructor.
     *
     * @param string $name
     */
    protected function __construct(public readonly string $name)
    {
    }

    /**
     * Returns all case names as an array.
     *
     * @return string[]
     */
    public static function names(): array
    {
        return array_values(static::toArray());
    }

    /**
     * Generates a list of cases (PureEnum instances) on an enum.
     *
     * @return static[]
     */
    public static function cases(): array
    {
        return array_values(static::all());
    }

    /**
     * Generates an array of cases (PureEnum instances) on an enum with name as the key.
     *
     * @return array<string, static>
     */
    final public static function all(): array
    {
        if (! isset(self::$wholeCases[static::class])) {
            static::init();
        }
        return self::$wholeCases[static::class];
    }

    /**
     * Maps a name string to an enum instance or null.
     *
     * @param string $name
     * @return static|null
     */
    public static function tryOf(string $name): ?static
    {
        return static::all()[$name] ?? null;
    }

    /**
     * Maps a name string to an enum instance.
     *
     * @param string $name
     * @return static
     * @throws ValueError
     */
    public static function of(string $name): static
    {
        if ($case = static::tryOf($name)) {
            return $case;
        }
        throw new ValueError();
    }

    /**
     * Handles magic static calls to enum case names.
     *
     * @param string $name
     * @param array<mixed> $_arguments
     * @return static
     * @throws Error
     */
    public static function __callStatic(string $name, array $_arguments): static
    {
        if (static::enableCaseNameStaticCall() && $case = static::tryOf($name)) {
            return $case;
        }
        throw new Error(sprintf('Call to undefined method %s::%s()', get_called_class(), $name));
    }

    /**
     * Initializes pure enum cases.
     */
    protected static function init(): void
    {
        $cases = [];

        foreach (static::toArray() as $name) {
            if (! static::validateAsCaseName($name)) {
                throw new LogicException("Invalid case name: $name");
            }
            if (isset($cases[$name])) {
                throw new LogicException("Duplicate case name: $name");
            }
            $cases[$name] = new static($name);
        }

        self::$wholeCases[static::class] = $cases;
    }

    /**
     * Validates the given name as a valid case name.
     *
     * The name must be a valid user-defined function name in PHP.
     * @see https://www.php.net/manual/en/functions.user-defined.php
     *
     * @param mixed $name
     * @return bool
     */
    final protected static function validateAsCaseName(mixed $name): bool
    {
        if (! is_string($name)) {
            return false;
        }
        if (static::enableCaseNameStaticCall()) {
            if (method_exists(static::class, $name)) {
                throw new LogicException("Case name conflicts with existing method: $name");
            }
            return (bool) preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $name);
        }
        return true;
    }

    /**
     * Determines if magic static calls to case names are enabled.
     *
     * @return bool
     */
    protected static function enableCaseNameStaticCall(): bool
    {
        return true;
    }
}
