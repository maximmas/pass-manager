<?php

namespace Tests;


use PHPUnit\Framework\TestCase;
use PassManager\Validator;


/**
 * class under test: PassManager\Validator
 *
 */
class ValidatorTest extends TestCase
{

    public function setUp(): void
    {

    }


    /**
     * @covers \PassManager\Validator
     *
     * @return void
     *
     */
    public function testIsFileAvailable1()
    {
        $file = 'data/pswd.txt';
        $expect = [true, ''];
        $response = Validator::isFileAvailable($file);
        self::assertSame($expect, $response);
    }


    /**
     * @covers \PassManager\Validator
     *
     * @return void
     *
     */
    public function testIsFileAvailable2()
    {
        $file = 'data1/maxim1.txt';
        $expect = [false, "File doesn't exists"];
        $response = Validator::isFileAvailable($file);
        self::assertSame($expect, $response);
    }

}
