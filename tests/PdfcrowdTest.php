<?php

namespace Swis\PdfcrowdClient\Tests;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_MockObject_MockObject;
use Swis\PdfcrowdClient\Exceptions\PdfcrowdException;
use Swis\PdfcrowdClient\Http\RequestInterface;
use Swis\PdfcrowdClient\Pdfcrowd;

class PdfcrowdTest extends BaseTestCase
{
    /** @var Pdfcrowd */
    protected $pdfcrowd;

    /** @var PHPUnit_Framework_MockObject_MockObject[]  */
    protected $requestMocks = [];

    public function setUp()
    {
        $this->pdfcrowd = new Pdfcrowd('username', 'key');
    }

    public function setRequestMocks(int $numberOfMocks)
    {
        for ($i=0; $i<$numberOfMocks; $i++) {
            $this->requestMocks[$i] = $this->createMock(RequestInterface::class);
        }

        $factory = new MockRequestFactory($this->requestMocks);

        $this->pdfcrowd->setRequestFactory($factory);
    }

    public function testConvertHtmlThrowsAnErrorWhenNoSrcIsProvided()
    {
        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionMessage('convertHTML(): the src parameter must not be empty');

        $this->pdfcrowd->convertHtml(null);
    }

    public function testSuccessfulConvertHtml()
    {
        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->once())
                    ->method('setUrl')
                    ->with('https://pdfcrowd.com/api/pdf/convert/html/');

        $this->requestMocks[0]->expects($this->once())
                    ->method('execute')
                    ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
                    ->method('getHttpStatusCode')
                    ->willReturn(200);

        $this->requestMocks[0]->expects($this->once())
                    ->method('close');

        $pdf = $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');

        $this->assertEquals($pdf, '123123');
    }

    // todo: add a test for convertHtml using an outstream

    // todo: add a test for convertUri

    // todo: add a test for convertFile

    public function testRetrieveAvailableTokens()
    {
        $this->pdfcrowd->trackTokens(true);

        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->once())
            ->method('setUrl')
            ->with('https://pdfcrowd.com/api/user/username/tokens/');

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->requestMocks[0]->expects($this->once())
            ->method('close');

        $tokens = $this->pdfcrowd->numTokens();

        $this->assertEquals($tokens, '123123');
    }

    public function testItTracksTokensBeforeAndAfterConvertHtml()
    {
        $this->pdfcrowd->trackTokens(true);

        $this->setRequestMocks(3);

        // first request should get number of tokens before actual request

        $this->requestMocks[0]->expects($this->once())
            ->method('setUrl')
            ->with('https://pdfcrowd.com/api/user/username/tokens/');

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('1337');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->requestMocks[0]->expects($this->once())
            ->method('close');


        // second request should convert html

        $this->requestMocks[1]->expects($this->once())
            ->method('setUrl')
            ->with('https://pdfcrowd.com/api/pdf/convert/html/');

        $this->requestMocks[1]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[1]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->requestMocks[1]->expects($this->once())
            ->method('close');


        // third request should get number of tokens after actual request

        $this->requestMocks[2]->expects($this->once())
            ->method('setUrl')
            ->with('https://pdfcrowd.com/api/user/username/tokens/');

        $this->requestMocks[2]->expects($this->once())
            ->method('execute')
            ->willReturn('1333');

        $this->requestMocks[2]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->requestMocks[2]->expects($this->once())
            ->method('close');


        $pdf = $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');

        $this->assertEquals($pdf, '123123');
        $this->assertEquals($this->pdfcrowd->getUsedTokens(), 4);
    }

    public function testItFailsToGetUsedTokensWithoutTrackingTokens()
    {
        $this->pdfcrowd->trackTokens(false);

        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionMessage('getUsedTokens() only works if you enable tracking tokens with trackTokens(true)');

        $this->pdfcrowd->getUsedTokens();

        $this->fail('getUsedTokens() should have thrown an exception');
    }

    public function testItFailsToGetUsedTokensWithoutAConversion()
    {
        $this->pdfcrowd->trackTokens(true);

        $this->setRequestMocks(1);

        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionMessage('getUsedTokens() should not be called on its own, call a convert call first.');

        $this->pdfcrowd->getUsedTokens();

        $this->fail('getUsedTokens() should have thrown an exception');
    }

    public function testFailedConvertHtmlWithGuzzleException()
    {
        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willThrowException(
                new RequestException(
                    'MyRequestExceptionMessage',
                    new Request('POST', 'http://temp')
                )
            );

        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage("Unknown error during request to Pdfcrowd");

        $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testFailedConvertHtmlWithInvalidHttpStatusCode()
    {
        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('myErorMessage');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(404);

        $this->requestMocks[0]->expects($this->once())
            ->method('close');

        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("myErorMessage");

        $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testPostWithTimeout()
    {
        $this->pdfcrowd->setTimeout(123);

        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->once())
            ->method('setTimeout')
            ->with(123);

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testPostWithUserAgent()
    {
        $this->pdfcrowd->setUserAgent('my-user-agent');

        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->once())
            ->method('setUserAgent')
            ->with('my-user-agent');

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testPostUsesSSLByDefault()
    {
        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->once())
            ->method('setVerifySsl')
            ->with(true);

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testPostWithoutSSL()
    {
        $this->pdfcrowd->useSSL(false);

        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->once())
            ->method('setVerifySsl')
            ->with(false);

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testPostWithProxyAndPassword()
    {
        $this->pdfcrowd->setProxy('my-proxy-name', 123, 'my-username', 'my-password');

        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->once())
            ->method('setProxy')
            ->with('my-proxy-name', '123');

        $this->requestMocks[0]->expects($this->once())
            ->method('setProxyAuth')
            ->with('my-username', 'my-password');

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    /**
     * @dataProvider postBodyParametersDataProvider
     *
     * @param string $classMethod
     * @param array  $parameters
     * @param string $postBodyKey
     * @param        $postBodyValue
     */
    public function testPostBodyParameters(string $classMethod, array $parameters, string $postBodyKey, $postBodyValue)
    {
        $this->pdfcrowd->{$classMethod}(...$parameters);

        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($spy = $this->once())
            ->method('setBody');

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');

        $this->assertPostBodyIncludes($spy, $postBodyKey, $postBodyValue);
    }

    public function postBodyParametersDataProvider()
    {
        return [
            ['setWatermarkInBackground', [true], 'watermark_in_background', 1],
            ['setWatermarkRotationsetWatermarkRotation', [45], 'watermark_rotation', 45],
            ['setWatermark', ['http://url/to/watermark', 123, 456], 'watermark_url', 'http://url/to/watermark'],
            ['setWatermark', ['http://url/to/watermark', 123, 456], 'watermark_offset_x', 123],
            ['setWatermark', ['http://url/to/watermark', 123, 456], 'watermark_offset_y', 456],
            ['setHeaderFooterPageExcludeList', ['1,-1'], 'header_footer_page_exclude_list', '1,-1'],
            ['setPageWidth', ['100'], 'width', '100'],
            ['setPageHeight', ['100'], 'height', '100'],
        ];
    }

    public function testUnsetBodyParameter()
    {
        $this->pdfcrowd->setWatermarkInBackground(true);

        $this->pdfcrowd->setWatermarkInBackground(false);

        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($spy = $this->once())
            ->method('setBody');

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');

        $this->assertPostBodyDoesNotInclude($spy, 'watermark_in_background');
    }
}
