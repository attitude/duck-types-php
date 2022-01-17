<?php

namespace Duck\Types\Tests\Functional\Todo;

use Duck\Types\Type;
use PHPUnit\Framework\TestCase;

class ExistentialNullTest extends TestCase
{
    /**
     * Todo: existential doesnt match 'null' (like isset())
     *
     * @test
     */
    public function shouldExistentialAllowNull()
    {
        self::markTestIncomplete();

        self::assertTrue(Type::is('*', null));
    }
}
