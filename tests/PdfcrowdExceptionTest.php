<?php

namespace Swis\PdfcrowdClient\Tests;

use PHPUnit\Framework\TestCase;
use Swis\PdfcrowdClient\Exceptions\PdfcrowdException;

class PdfcrowdExceptionTest extends TestCase
{
    public function testItConvertsMesasgeToString()
    {
        $exception = new PdfcrowdException("MyMessage");

        $this->assertEquals("MyMessage\n", $exception->__toString());
    }

    public function testItConvertsMessageAndCodeToString()
    {
        $exception = new PdfcrowdException("MyMessage", 200);

        $this->assertEquals("[200] MyMessage\n", $exception->__toString());
    }
}
