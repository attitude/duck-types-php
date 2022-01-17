<?php

namespace Duck\Types\Tests\Functional;

use Duck\Types\IncompatibleTypeError;
use Duck\Types\Type;
use PHPUnit\Framework\TestCase;
use Duck\Types\Tests\Functional\TestHelper\AbstractValue;
use Duck\Types\Tests\Functional\TestHelper\CorrectValue;
use Duck\Types\Tests\Functional\TestHelper\WrongValue;

class CombinedAnnotationsTest extends TestCase
{

    public function arrayTypeChecks(): \Generator
    {
        yield from self::generateFromMultiple(
            'string list',
            'string[]',
            new CorrectValue(
                'integer indexed list with string values',
                ['foo', 'bar', 'baz']
            ),
            new CorrectValue(
                'associative array with string keys and values',
                ['a' => 'foo', 'b' => 'bar']
            ),
            new WrongValue(
                'integer indexed list with string and int values',
                ['foo', 'bar', 123]
            ),
        );

        yield from self::generateFromMultiple(
            'string int list',
            '(string|int)[]',
            new CorrectValue(
                'integer indexed list with string and int values',
                ['foo', 'bar', 123]
            ),
            new CorrectValue(
                'integer indexed list with string values',
                ['foo', 'bar', 'baz']
            ),
            new WrongValue(
                'list with string and int and null values',
                ['foo', null, 123]
            ),
        );

        $valuesForStringNullList = [
            new CorrectValue(
                'integer indexed list with string and null values',
                ['foo', null, 'bar']
            ),
            new CorrectValue(
                'integer indexed list with string values',
                ['foo', 'bar', 'baz']
            ),
            new WrongValue(
                'list with string and int and null values',
                ['foo', null, 123]
            ),
        ];

        yield from self::generateFromMultiple(
            'alternate syntax: string null list',
            'Array<?string>',
            ...$valuesForStringNullList
        );

        yield from self::generateFromMultiple(
            'string null list',
            '(?string)[]',
            ...$valuesForStringNullList
        );
    }

    public function arrayObjectTypeChecks(): \Generator
    {
        yield from self::generateFromMultiple(
            'exact array or object shape',
            '{|foo: string, bar: int|}',
            new CorrectValue(
                'associative matching keys and values',
                ['foo' => 'baz', 'bar' => 10]
            ),
            new CorrectValue(
                'object matching public properties and values',
                new class {
                    public $foo = 'baz';
                    public $bar = 10;
                }
            ),
            new WrongValue(
                'additional array key',
                ['foo' => 'baz', 'bar' => 10, 'buz' => 'bat']
            ),
            new WrongValue(
                'additional object prop',
                new class {
                    public $foo = 'baz';
                    public $bar = 10;
                    public $buz = 'bat';
                }
            ),
        );

        yield from self::generateFromMultiple(
            'exact array or object shape with null able values',
            '{|foo: ?int, baz: ?int |}',
            new CorrectValue(
                'array value is null',
                ['foo' => 124, 'baz' => null]
            ),
            new CorrectValue(
                'array value is not null',
                ['foo' => 124, 'baz' => 13]
            ),
            new WrongValue(
                'array value doesnt match type',
                ['foo' => 124, 'baz' => "13"]
            ),
        );
    }

    public function nestedArrayChecks(): \Generator
    {
        yield from self::generateFromMultiple(
            'nested array values check: list of int[]',
            '(int[])[]',
            new CorrectValue(
                'list of int[]',
                [[1, 2, 3], [1, 2]]
            ),
            new CorrectValue(
                'list of int[] and empty array',
                [[1, 2, 3], [1, 2], []]
            ),
            new WrongValue(
                'list of (string|int)[]',
                [[1, 2, 3], [1, 'two']]
            ),
            new WrongValue(
                'deeper nested list of int arrays',
                [[1, 2, 3], [[1, 1]]]
            ),
        );

        yield from self::generateFromMultiple(
            'nested exact array',
            '{|foo: {|bar: string, baz:int|} |}',
            new CorrectValue(
                'array with correctly nested keys and values',
                ['foo' => [
                    'bar' => "hello",
                    'baz' => 123
                ]]
            ),
            new WrongValue(
                'nested array has wrong value type',
                ['foo' => [
                    'bar' => "hello",
                    'baz' => '123'
                ]]
            ),
            new WrongValue(
                'nested array has extra key',
                ['foo' => [
                    'bar' => "hello",
                    'baz' => 123,
                    'buz' => null,
                ]]
            )
        );
    }

    /**
     * @dataProvider nestedArrayChecks
     * @dataProvider arrayObjectTypeChecks
     * @dataProvider arrayTypeChecks
     * @test
     */
    public function assertDuckTypesWorkingAsExpected(string $annotation, AbstractValue $value)
    {
        if ($value instanceof WrongValue) {
            self::expectException(IncompatibleTypeError::class);
        }

        $result = Type::is($annotation, $value->value);

        if ($value instanceof CorrectValue) {
            self::assertTrue($result);
        }
    }

    protected static function generateFromMultiple(string $annotationDescription, string $annotation, AbstractValue ...$abstractValues): \Generator
    {
        if (empty($abstractValues)) {
            throw new \InvalidArgumentException("At least one abstractValues need to be specified");
        }
        foreach ($abstractValues as $index => $abstractValue) {
            yield "'$annotationDescription'[$index]: '{$abstractValue->description}'" => [
                'annotation' => $annotation,
                'abstractValue' => $abstractValue,
            ];
        }
    }
}
