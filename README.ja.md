# PHP Enumeration (日本語版)

[![Latest Stable Version](https://poser.pugx.org/mimosafa/php-enumeration/v/stable)](https://packagist.org/packages/mimosafa/php-enumeration)
[![License](https://poser.pugx.org/mimosafa/php-enumeration/license)](https://packagist.org/packages/mimosafa/php-enumeration)

PHP 8.1+で、ネイティブのEnum（列挙型）の機能を超えた、継承可能でリッチな機能を持つ列挙型を作成するためのライブラリです。

---

## なぜこのパッケージが必要か？

PHP 8.1でネイティブの列挙型が導入されました。これは言語にとって素晴らしい追加機能ですが、いくつかの制限もあります。

- クラスを継承（`extends`）することができない。
- Backed Enumでサポートされる値の型は `string` か `int` に限定される。

このパッケージは、これらの制限を克服するための基底クラスとトレイトを提供し、PHP 8.1以上のプロジェクトで、より柔軟かつ強力に列挙型を扱うための方法を提供します。

主な特徴は以下の通りです。

- **継承**: Enumを継承して、より複雑で再利用性の高い構造を作ることができます。
- **便利なファクトリメソッド**: `::of()` や `::tryOf()` を使って、ケース名からインスタンスを簡単に取得できます。
- **柔軟な値**: Backed Enumの値として、`string` や `int` だけでなく、任意のスカラー値を使用できます。
- **動的なケース制御**: サブクラスが、親クラスからどのケースを継承するかを正確にコントロールできます。

## ネイティブPHP Enumとの比較

このライブラリは強力な機能を提供しますが、ネイティブPHP Enumとの違いを理解し、ニーズに合ったツールを選択することが重要です。

| 機能 / 側面                | ネイティブPHP Enum (PHP 8.1+) | このライブラリ (`mimosafa/php-enumeration`) |
|----------------------------|-------------------------------|-----------------------------------------------|
| **継承**                   | ❌ 非対応                     | ✅ 対応                                       |
| **柔軟なBacked Value**     | ❌ `string` または `int` のみ | ✅ 任意のスカラー値 (string, int, float, bool) |
| **名前によるインスタンス取得**| ✅ (直接静的アクセス) | ✅ 対応 (`::of()` / `::tryOf()` 経由)                  |
| **動的なケース定義**       | ❌ 非対応                     | ✅ 対応 (`toArray()` または `EnumerateConstantsTrait` 経由)      |
| **`match` 式**             | ✅ 対応                       | ❌ 直接は非対応                               |
| **型ヒント**               | `enum` キーワード             | クラス名 (例: `PureEnum`, `BackedEnum`)       |
| **組み込みの `cases()`**   | ✅ 対応                       | ✅ 対応 (カスタム実装経由)                    |

継承や柔軟なBacked Valueのような高度な機能が必要な場合は、このライブラリを選択してください。`match` 式のサポートや厳密な `string`/`int` のBacked Valueが優先されるシンプルなユースケースでは、ネイティブPHP Enumで十分かもしれません。

## インストール

Composerでパッケージをインストールできます。

```bash
composer require mimosafa/php-enumeration
```

## 使い方

### Pure Enum（純粋な列挙型）

値を持たないシンプルな列挙型を作成します。`toArray()` メソッドは、ケースを動的に定義することを可能にします。

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

**動的なケース定義の例:**

例えば、ディレクトリ内のファイル名を読み込むことで、Enumのケースを動的に定義できます。

```php
use Enumeration\PureEnum;
use function Safe\glob; // 堅牢性のためにSafe\globを想定

/**
 * @method static self BACKED_ENUM_PHP()
 * @method static self ENUMERATE_CONSTANTS_TRAIT_PHP()
 * @method static self PURE_ENUM_PHP()
 */
class SourceFiles extends PureEnum
{
    public static function toArray(): array
    {
        $files = glob(__DIR__ . '/src/*.php'); // パスは適宜調整
        return array_map(fn($file) => strtoupper(str_replace('.', '_', basename($file))), $files);
    }
}

// 使用例:
// assert(SourceFiles::PURE_ENUM_PHP() instanceof SourceFiles);
```

### Backed Enum（値を持つ列挙型）

スカラー値を持つ列挙型を作成します。

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

### `EnumerateConstantsTrait` による継承

このライブラリの真価はここにあります。ケースをクラス定数として定義し、継承を利用して強力でドメイン固有の列挙型を構築できます。

`EnumerateConstantsTrait` は、クラス定数を自動的に列挙型のケースに変換します。

**1. 基底となるEnumを定義します:**

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

**2. 継承してケースを制御します:**

`UserRole` を継承しつつ、一部のケースのみを公開する特殊なEnumを作成できます。

```php
// SuperAdminを除外した、一般的なサイトユーザー向けのEnum
class SiteUserRole extends UserRole
{
    protected static function excludedConstantsFromEnumeration(): array
    {
        return ['SuperAdmin'];
    }
}

// 管理者権限のみに絞ったEnum
class AdminRole extends UserRole
{
    protected static function includedConstantsFromEnumeration(): array
    {
        return ['Admin', 'SuperAdmin'];
    }
}
```

**3. アプリケーションで利用します:**

```php
// [1, 2, 3] が返る
SiteUserRole::values();

// ['Admin', 'SuperAdmin'] が返る
AdminRole::names();

// AdminRoleに'Reader'は含まれないため、ValueErrorがスローされる
AdminRole::of('Reader');

// 親クラスを型ヒントとして利用できる
function grantPermission(UserRole $role)
{
    // ...
}

// どちらも有効
grantPermission(SiteUserRole::Admin());
grantPermission(AdminRole::SuperAdmin());
```

## ライセンス

MITライセンス（MIT）です。詳細は [License File](LICENSE) をご覧ください。
