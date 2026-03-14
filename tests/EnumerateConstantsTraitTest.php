<?php

namespace Tests {

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

}

namespace Tests\EnumerateConstantsTraitTest {

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
}
