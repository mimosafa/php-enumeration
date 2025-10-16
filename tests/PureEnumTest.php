<?php

namespace Tests {

    use Tests\PureEnumTest\DisabledCaseNameStaticCallSuit;
    use Tests\PureEnumTest\Suit;
    use Error;
    use ValueError;

    test('all', function () {
        $all = Suit::all();

        foreach ($all as $name => $case) {
            expect($case)->toBeInstanceOf(Suit::class);
            expect($case->name)->toBe($name);
        }
    });

    test('try of', function () {
        $names = Suit::toArray();
        $name = $names[rand(0, count($names) - 1)];

        $case = Suit::tryOf($name);
        expect($case)->toBeInstanceOf(Suit::class);
        expect($case->name)->toBe($name);

        expect(Suit::tryOf('not_' . $name))->toBeNull();
    });

    test('of', function () {
        $names = Suit::toArray();
        $name = $names[rand(0, count($names) - 1)];

        $case = Suit::of($name);
        expect($case)->toBeInstanceOf(Suit::class);
        expect($case->name)->toBe($name);

        expect(fn() => Suit::of('not_' . $name))->toThrow(ValueError::class);
    });

    test('get instance by name string static method', function () {
        $clubs = Suit::Clubs();
        expect($clubs)->toBeInstanceOf(Suit::class);
        expect($clubs->name)->toBe('Clubs');
    });

    test('disable case name static call', function () {
        expect(fn() => DisabledCaseNameStaticCallSuit::Hearts())->toThrow(Error::class);
    });

}

namespace Tests\PureEnumTest {

    use Enumeration\PureEnum;

    /**
     * Mock class
     */
    class Suit extends PureEnum
    {
        public static function toArray(): array
        {
            return [
                'Hearts',
                'Diamonds',
                'Clubs',
                'Spades',
            ];
        }
    }

    class DisabledCaseNameStaticCallSuit extends Suit
    {
        protected static function enableCaseNameStaticCall(): bool
        {
            return false;
        }
    }
}
