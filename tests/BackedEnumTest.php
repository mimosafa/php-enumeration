<?php

namespace Tests {

    use Tests\BackedEnumTest\Suit;
    use ValueError;

    beforeEach(function () {
        $array = Suit::toArray();
        $names = array_keys($array);
        $this->name = $names[rand(0, count($names) - 1)];
        $this->value = $array[$this->name];
    });

    test('try from', function () {
        $case = Suit::tryFrom($this->value);
        expect($case)->toBeInstanceOf(Suit::class)
            ->and($case->name)->toBe($this->name)
            ->and($case->value)->toBe($this->value);

        expect(Suit::tryFrom('not_' . $this->value))->toBeNull();
    });

    test('from', function () {
        $case = Suit::from($this->value);
        expect($case)->toBeInstanceOf(Suit::class)
            ->and($case->name)->toBe($this->name)
            ->and($case->value)->toBe($this->value);

        expect(fn() => Suit::from('not_' . $this->value))->toThrow(ValueError::class);
    });

}

namespace Tests\BackedEnumTest {

    use Enumeration\BackedEnum;

    /**
     * @extends BackedEnum<Suit>
     */
    class Suit extends BackedEnum
    {
        public static function toArray(): array
        {
            return [
                'Hearts' => 'H',
                'Diamonds' => 'D',
                'Club' => 'C',
                'Spades' => 'S',
            ];
        }
    }
}
