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

        $requestMock->expects($this->at(0))
                    ->method('setOption')
                    ->with(CURLOPT_URL, 'https://pdfcrowd.com/api/pdf/convert/html/');

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

    // todo: add a test for convertHtml using an outstream

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

        $pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testFailedConvertHtmlWithoutErrorNumber()
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
            ->willReturn(0);

        $requestMock->expects($this->once())
            ->method('close');

        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("123123");

        $pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testPostWithTimeout()
    {
        $pdfcrowd = new Pdfcrowd('username', 'key');
        $pdfcrowd->setTimeout(123);

        $requestMock = $this->createMock(RequestInterface::class);

        $factory = new MockRequestFactory([$requestMock]);
        $pdfcrowd->setRequestFactory($factory);

        $requestMock->expects($this->at(9))
            ->method('setOption')
            ->with(CURLOPT_TIMEOUT, 123);

        $requestMock->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $requestMock->expects($this->once())
            ->method('getInfo')
            ->willReturn(200);

        $pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testPostWithUserAgent()
    {
        $pdfcrowd = new Pdfcrowd('username', 'key');
        $pdfcrowd->setUserAgent('my-user-agent');

        $requestMock = $this->createMock(RequestInterface::class);

        $factory = new MockRequestFactory([$requestMock]);
        $pdfcrowd->setRequestFactory($factory);

        $requestMock->expects($this->at(8))
            ->method('setOption')
            ->with(CURLOPT_USERAGENT, 'my-user-agent');

        $requestMock->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $requestMock->expects($this->once())
            ->method('getInfo')
            ->willReturn(200);

        $pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testPostWithMatermarkInBackground()
    {
        $pdfcrowd = new Pdfcrowd('username', 'key');
        $pdfcrowd->setWatermarkInBackground(true);

        $requestMock = $this->createMock(RequestInterface::class);

        $factory = new MockRequestFactory([$requestMock]);
        $pdfcrowd->setRequestFactory($factory);

        $requestMock->expects($spy = $this->any())
            ->method('setOption');

        $requestMock->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $requestMock->expects($this->once())
            ->method('getInfo')
            ->willReturn(200);

        $pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');

        $this->assertPostfieldParameterIncludes($spy, 'watermark_in_background', 1);
    }

    public function testPostUsesSSLByDefault()
    {
        $pdfcrowd = new Pdfcrowd('username', 'key');

        $requestMock = $this->createMock(RequestInterface::class);

        $factory = new MockRequestFactory([$requestMock]);
        $pdfcrowd->setRequestFactory($factory);

        $requestMock->expects($this->at(9))
            ->method('setOption')
            ->with(CURLOPT_SSL_VERIFYPEER, true);

        $requestMock->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $requestMock->expects($this->once())
            ->method('getInfo')
            ->willReturn(200);

        $pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testPostWithoutSSL()
    {
        $pdfcrowd = new Pdfcrowd('username', 'key');
        $pdfcrowd->useSSL(false);

        $requestMock = $this->createMock(RequestInterface::class);

        $factory = new MockRequestFactory([$requestMock]);
        $pdfcrowd->setRequestFactory($factory);

        $requestMock->expects($this->at(9))
            ->method('setOption')
            ->with(CURLOPT_SSL_VERIFYPEER, false);

        $requestMock->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $requestMock->expects($this->once())
            ->method('getInfo')
            ->willReturn(200);

        $pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testPostWithProxyAndPassword()
    {
        $pdfcrowd = new Pdfcrowd('username', 'key');
        $pdfcrowd->setProxy('my-proxy-name', 123, 'my-username', 'my-password');

        $requestMock = $this->createMock(RequestInterface::class);

        $factory = new MockRequestFactory([$requestMock]);
        $pdfcrowd->setRequestFactory($factory);

        $requestMock->expects($this->at(10))
            ->method('setOption')
            ->with(CURLOPT_PROXY, 'my-proxy-name:123');

        $requestMock->expects($this->at(11))
            ->method('setOption')
            ->with(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $requestMock->expects($this->at(12))
            ->method('setOption')
            ->with(CURLOPT_PROXYUSERPWD, 'my-username:my-password');

        $requestMock->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $requestMock->expects($this->once())
            ->method('getInfo')
            ->willReturn(200);

        $pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount $spy
     * @param string                                                $option
     * @param mixed                                                 $value
     *
     * @internal param string $string
     */
    private function assertPostfieldParameterIncludes($spy, string $option, $value)
    {
        /** @var \PHPUnit_Framework_MockObject_Invocation_Object $invocation */
        foreach ($spy->getInvocations() as $invocation) {
            if ($invocation->methodName !== 'setOption') {
                continue;
            }

            if (!isset($invocation->parameters[0]) || !isset($invocation->parameters[1]) || $invocation->parameters[0] !== CURLOPT_POSTFIELDS) {
                continue;
            }

            $needle = urlencode($option).'='.urlencode($value);

            $this->assertContains($needle, $invocation->parameters[1], 'String '.$needle.' not found in postfield parameter '.$invocation->parameters[1]);
            return;
        }

        $this->fail('No postfield parameter found among spy\'s invocations');
    }
}
