<?php

namespace Tests {

    use LogicException;
    use Tests\EnumerateConstantsTraitTest\SuitAttributeExcludeReds;
    use Tests\EnumerateConstantsTraitTest\SuitAttributeIncludeHeartsExcludeReds;
    use Tests\EnumerateConstantsTraitTest\SuitAttributeIncludeReds;
    use Tests\EnumerateConstantsTraitTest\SuitAttributeNoDuplicate;
    use Tests\EnumerateConstantsTraitTest\Suit;
    use Tests\EnumerateConstantsTraitTest\SuitExcludeReds;
    use Tests\EnumerateConstantsTraitTest\SuitIncludeReds;
    use Tests\EnumerateConstantsTraitTest\SuitOnlyHearts;

    test('to array', function () {
        $expected = [
            'Hearts' => 'H',
            'Diamonds' => 'D',
            'Clubs' => 'C',
            'Spades' => 'S',
        ];
        expect(Suit::toArray())->toBe($expected);
    });

    test('included constants from enumeration', function () {
        $expected = [
            'Hearts' => 'H',
            'Diamonds' => 'D',
        ];
        expect(SuitIncludeReds::toArray())->toBe($expected);
    });

    test('excluded constants from enumeration', function () {
        $expected = [
            'Clubs' => 'C',
            'Spades' => 'S',
        ];
        expect(SuitExcludeReds::toArray())->toBe($expected);
    });

    test('including has priority over excluding', function () {
        $expected = [
            'Hearts' => 'H',
        ];
        expect(SuitOnlyHearts::toArray())->toBe($expected);
    });

    test('included constants with attribute', function () {
        $expected = [
            'Hearts' => 'H',
            'Diamonds' => 'D',
        ];
        expect(SuitAttributeIncludeReds::toArray())->toBe($expected);
    });

    test('excluded constants with attribute', function () {
        $expected = [
            'Clubs' => 'C',
            'Spades' => 'S',
        ];
        expect(SuitAttributeExcludeReds::toArray())->toBe($expected);
    });

    test('including has priority over excluding with attributes', function () {
        $expected = [
            'Hearts' => 'H',
        ];
        expect(SuitAttributeIncludeHeartsExcludeReds::toArray())->toBe($expected);
    });

    test('disallow duplicate values with attribute', function () {
        expect(fn () => SuitAttributeNoDuplicate::toArray())->toThrow(LogicException::class);
    });

}

namespace Tests\EnumerateConstantsTraitTest {

    use Enumeration\Attributes\AllowDuplicateValues;
    use Enumeration\Attributes\ExcludeConstants;
    use Enumeration\Attributes\IncludeConstants;
    use Enumeration\EnumerateConstantsTrait;

    class Suit
    {
        use EnumerateConstantsTrait;

        public const Hearts = 'H';
        public const Diamonds = 'D';
        public const Clubs = 'C';
        public const Spades = 'S';
    }

    class SuitIncludeReds extends Suit
    {
        protected static function includedConstantsFromEnumeration(): array
        {
            return [
                'Hearts',
                'Diamonds',
            ];
        }
    }

    class SuitExcludeReds extends Suit
    {
        protected static function excludedConstantsFromEnumeration(): array
        {
            return [
                'Hearts',
                'Diamonds',
            ];
        }
    }

    class SuitOnlyHearts extends SuitExcludeReds
    {
        protected static function includedConstantsFromEnumeration(): array
        {
            return [
                'Hearts',
            ];
        }
    }

    #[IncludeConstants('Hearts', 'Diamonds')]
    class SuitAttributeIncludeReds extends Suit
    {
    }

    #[ExcludeConstants('Hearts', 'Diamonds')]
    class SuitAttributeExcludeReds extends Suit
    {
    }

    #[IncludeConstants('Hearts')]
    #[ExcludeConstants('Hearts', 'Diamonds')]
    class SuitAttributeIncludeHeartsExcludeReds extends Suit
    {
    }

    #[AllowDuplicateValues(false)]
    class SuitAttributeNoDuplicate
    {
        use EnumerateConstantsTrait;

        public const Hearts = 'R';
        public const Diamonds = 'R';
    }
}
