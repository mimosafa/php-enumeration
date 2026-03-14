<?php

declare(strict_types=1);

namespace Enumeration;

use LogicException;
use ValueError;

/**
 * A base class for creating inheritable, backed enumerations.
 *
 * For IDE autocompletion with magic static calls, add @method annotations to
 * the child class PHPDoc.
 *
 * Example child class annotations:
 * @method static self Hearts()
 * @method static self Diamonds()
 */
abstract class BackedEnum extends PureEnum
{
    /**
     * Returns all possible cases as an associative array [caseName => caseValue].
     *
     * @return array<string, string|int|float|bool>
     */
    abstract public static function toArray(): array;

    /**
     * Protected constructor.
     *
     * @param string $name
     * @param string|int|float|bool $value
     */
    protected function __construct(
        public readonly string $name,
        public readonly string|int|float|bool $value,
    )
    {
    }

    /**
     * Returns all case names as an array.
     *
     * @return string[]
     */
    public static function names(): array
    {
        return array_keys(static::toArray());
    }

    /**
     * Returns all case values as an array.
     *
     * @return array<string|int|float|bool>
     */
    public static function values(): array
    {
        return array_values(static::toArray());
    }

    /**
     * Maps a scalar value to an enum instance or null.
     *
     * @param string|int|float|bool $value
     * @return static|null
     */
    public static function tryFrom($value): ?static
    {
        $name = array_search($value, static::toArray(), true);
        return $name === false ? null : static::of($name);
    }

    /**
     * Maps a scalar value to an enum instance.
     *
     * @param string|int|float|bool $value
     * @return static
     * @throws ValueError
     */
    public static function from($value): static
    {
        if ($case = static::tryFrom($value)) {
            return $case;
        }
        throw new ValueError();
    }

    /**
     * Validates if the given value is a valid case value.
     *
     * @param string|int|float|bool $value
     * @return bool
     */
    public static function validate($value): bool
    {
        return static::validateAsScalarEquivalent($value) && in_array($value, static::toArray(), true);
    }

    /**
     * Initializes backed enum cases.
     */
    protected static function init(): void
    {
        $cases = [];
        $values = [];

        foreach (static::toArray() as $name => $value) {
            if (! static::validateAsCaseName($name)) {
                throw new LogicException("Invalid case name: $name");
            }
            if (isset($cases[$name])) {
                throw new LogicException("Duplicate case name: $name");
            }
            if (! static::validateAsScalarEquivalent($value)) {
                throw new LogicException("Value for case $name is not a scalar.");
            }
            if (in_array($value, $values, true)) {
                throw new LogicException("Duplicate case value: " . (string) $value);
            }
            $cases[$name] = new static($name, $value);
            $values[] = $value;
        }

        parent::$wholeCases[static::class] = $cases;
    }

    /**
     * Validates if the given value is a scalar.
     *
     * @param mixed $value
     * @return bool
     */
    protected static function validateAsScalarEquivalent(mixed $value): bool
    {
        return is_scalar($value);
    }
}
