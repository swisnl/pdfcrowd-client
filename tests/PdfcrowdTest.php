<?php

namespace Swis\PdfcrowdClient\Tests;

use Swis\PdfcrowdClient\Exceptions\PdfcrowdException;
use Swis\PdfcrowdClient\Http\RequestInterface;
use Swis\PdfcrowdClient\Pdfcrowd;

class PdfcrowdTest extends \PHPUnit\Framework\TestCase
{
    public function testConvertHtmlThrowsAnErrorWhenNoSrcIsProvided()
    {
        $pdfcrowd = new Pdfcrowd('username', 'key');

        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionMessage('convertHTML(): the src parameter must not be empty');

        $pdfcrowd->convertHtml(null);
    }

    public function testSuccessfulConvertHtml()
    {
        $pdfcrowd = new Pdfcrowd('username', 'key');

        $requestMock = $this->createMock(RequestInterface::class);

        $factory = new MockRequestFactory([$requestMock]);
        $pdfcrowd->setRequestFactory($factory);

        $requestMock->expects($this->once())
                    ->method('execute')
                    ->willReturn('123123');

        $requestMock->expects($this->once())
                    ->method('getInfo')
                    ->willReturn(200);

        $requestMock->expects($this->once())
            ->method('close');

        $pdf = $pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');

        $this->assertEquals($pdf, '123123');
    }

    public function testFailedConvertHtml()
    {
        $pdfcrowd = new Pdfcrowd('username', 'key');

        $requestMock = $this->createMock(RequestInterface::class);

        $factory = new MockRequestFactory([$requestMock]);
        $pdfcrowd->setRequestFactory($factory);

        $requestMock->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $requestMock->expects($this->once())
            ->method('getInfo')
            ->willReturn(404);

        $requestMock->expects($this->once())
            ->method('getErrorMessage')
            ->willReturn('myErrorMessage');

        $requestMock->expects($this->once())
            ->method('getErrorNumber')
            ->willReturn(1337);

        $requestMock->expects($this->once())
            ->method('close');

        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionCode(1337);
        $this->expectExceptionMessage("myErrorMessage");

        $pdf = $pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');

        $this->assertEquals($pdf, '123123');
    }
}
