<?php

namespace Swis\PdfcrowdClient\Tests;

use Swis\PdfcrowdClient\Exceptions\PdfcrowdException;
use Swis\PdfcrowdClient\Pdfcrowd;

class PdfcrowdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test that true does in fact equal true
     */
    public function testConvertHtmlThrowsAnErrorWhenNoSrcIsProvided()
    {
        $pdfcrowd = new Pdfcrowd('username', 'password');

        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionMessage('convertHTML(): the src parameter must not be empty');

        $pdfcrowd->convertHtml(null);
    }

    // todo: add test for convertHtml with a mock of the RequestInterface,
    // then check that the RequestInterface is called with the correct values
}
