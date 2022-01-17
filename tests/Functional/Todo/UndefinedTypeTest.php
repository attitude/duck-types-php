<?php

namespace Duck\Types\Tests\Functional\Todo;

use Duck\Types\Type;
use PHPUnit\Framework\TestCase;

class UndefinedTypeTest extends TestCase
{
    /**
     * Todo:
     *
     * Has probably no use case, as it basically impossible to pass an undefined var (except with @)
     * @test
     */
    public function undefinedType()
    {
        // TODO: passing undefined var to functions only works with @ supress
        // or with reference &. But for the reference, every function must pass the
        // value by reference, so that this can work ?undefined
        //    $undefinedCheck = static function(&$var) {
        //        if (isset($var)) {
        //            throw ...
        //        }
        //        return true;
        //    };

        self::assertTrue(
            Type::is('undefined', @$undefinedVar)
        );
    }

    /**
     * Throws a php error, since $undefinedVar is not defined...
     *
     * @test
     */
    public function undefinedTypeWithNoSuppress()
    {
        self::markTestIncomplete();

        self::assertTrue(
            Type::is('undefined', $undefinedVar)
        );
    }
}
