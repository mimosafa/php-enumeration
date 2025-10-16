# PHP Enumeration

[日本語のREADMEはこちら](README.ja.md)

[![Latest Stable Version](https://poser.pugx.org/mimosafa/php-enumeration/v/stable)](https://packagist.org/packages/mimosafa/php-enumeration)
[![License](https://poser.pugx.org/mimosafa/php-enumeration/license)](https://packagist.org/packages/mimosafa/php-enumeration)

A powerful PHP 8.1+ library to create inheritable, feature-rich enumerations that extend beyond native enum capabilities.

---

## Why PHP Enumeration?

PHP 8.1 introduced native enumerations, which are a great addition to the language. However, they come with a few limitations:

- They cannot be extended (no `extends`).
- They are strictly typed as `string` or `int` for backed enums.

This package provides a set of base classes and traits to create enumerations that overcome these limitations, offering a more flexible and powerful way to work with enums in your PHP 8.1+ projects.

Key features include:

- **Inheritance**: Extend your enums to create more complex and reusable structures.
- **Convenient Factory Methods**: Get enum instances by case name using `::of()` and `::tryOf()`.
- **Flexible Backed Values**: Use any scalar value for your backed enums, not just `string` or `int`.
- **Dynamic Case Control**: Subclasses can precisely control which cases to inherit from a parent class.

## Comparison with Native PHP Enums

While this library offers powerful features, it's important to understand the differences and choose the right tool for your needs.

| Feature / Aspect           | Native PHP Enums (PHP 8.1+) | This Library (`mimosafa/php-enumeration`) |
|----------------------------|-----------------------------|-------------------------------------------|
| **Inheritance**            | ❌ Not supported            | ✅ Supported                              |
| **Flexible Backed Values** | ❌ `string` or `int` only   | ✅ Any scalar (string, int, float, bool)  |
| **Instance lookup by name**| ✅ (Direct static access) | ✅ Supported (via `::of()` / `::tryOf()`) |
| **Dynamic Case Definition**| ❌ Not supported            | ✅ Supported (via `toArray()` or `EnumerateConstantsTrait`) |
| **`match` Expression**     | ✅ Supported                | ❌ Not directly supported                 |
| **Type Hinting**           | `enum` keyword              | Class name (e.g., `PureEnum`, `BackedEnum`) |
| **Built-in `cases()`**     | ✅ Supported                | ✅ Supported (via custom implementation)  |

Choose this library if you need advanced features like inheritance or flexible backed values. For simpler use cases where `match` expression support and strict `string`/`int` backed values are preferred, native PHP Enums might be sufficient.

## Installation

You can install the package via composer:

```bash
composer require mimosafa/php-enumeration
```

## Usage

### Pure Enums

Create a simple enum without backed values. The `toArray()` method allows for dynamic definition of cases.

```php
use Enumeration\PureEnum;

/**
 * @method static self PENDING()
 * @method static self PUBLISHED()
 * @method static self ARCHIVED()
 */
class Status extends PureEnum
{
    public static function toArray(): array
    {
        return ['PENDING', 'PUBLISHED', 'ARCHIVED'];
    }
}

$status = Status::PUBLISHED();

assert($status->name === 'PUBLISHED');
assert(Status::of('PENDING') === Status::PENDING());
```

**Dynamic Case Definition Example:**

You can define enum cases dynamically, for instance, by reading file names from a directory:

```php
use Enumeration\PureEnum;
use function Safe\glob; // Assuming Safe\glob for robustness

/**
 * @method static self BACKED_ENUM_PHP()
 * @method static self ENUMERATE_CONSTANTS_TRAIT_PHP()
 * @method static self PURE_ENUM_PHP()
 */
class SourceFiles extends PureEnum
{
    public static function toArray(): array
    {
        $files = glob(__DIR__ . '/src/*.php'); // Adjust path as needed
        return array_map(fn($file) => strtoupper(str_replace('.', '_', basename($file))), $files);
    }
}

// Example usage:
// assert(SourceFiles::PURE_ENUM_PHP() instanceof SourceFiles);
```

### Backed Enums

Create enums with scalar values.

```php
use Enumeration\BackedEnum;

/**
 * @method static self Hearts()
 * @method static self Diamonds()
 * @method static self Clubs()
 * @method static self Spades()
 */
class Suit extends BackedEnum
{
    public static function toArray(): array
    {
        return [
            'Hearts'   => 'H',
            'Diamonds' => 'D',
            'Clubs'    => 'C',
            'Spades'   => 'S',
        ];
    }
}

$suit = Suit::Diamonds();

assert($suit->name === 'Diamonds');
assert($suit->value === 'D');
assert(Suit::from('S') === Suit::Spades());
```

### Inheritance with `EnumerateConstantsTrait`

This is where the magic happens. Define your cases as class constants and use inheritance to build powerful, domain-specific enums.

The `EnumerateConstantsTrait` automatically turns your class constants into enum cases.

**1. Define a base enum:**

```php
use Enumeration\BackedEnum;
use Enumeration\EnumerateConstantsTrait;

abstract class UserRole extends BackedEnum
{
    use EnumerateConstantsTrait;

    const Reader = 1;
    const Editor = 2;
    const Admin = 3;
    const SuperAdmin = 4;
}
```

**2. Extend and control the cases:**

Now, you can create specialized enums that inherit from `UserRole` but only expose a subset of the cases.

```php
// An enum for regular site roles, excluding SuperAdmin.
class SiteUserRole extends UserRole
{
    protected static function excludedConstantsFromEnumeration(): array
    {
        return ['SuperAdmin'];
    }
}

// An enum for administrative roles.
class AdminRole extends UserRole
{
    protected static function includedConstantsFromEnumeration(): array
    {
        return ['Admin', 'SuperAdmin'];
    }
}
```

**3. Use them in your application:**

```php
// Returns [1, 2, 3]
SiteUserRole::values();

// Returns ['Admin', 'SuperAdmin']
AdminRole::names();

// Throws a ValueError because 'Reader' is not in AdminRole
AdminRole::of('Reader');

// You can still use the parent class for type hinting
function grantPermission(UserRole $role)
{
    // ...
}

// Both are valid
grantPermission(SiteUserRole::Admin());
grantPermission(AdminRole::SuperAdmin());
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.