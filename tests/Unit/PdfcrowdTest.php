<?php

namespace Swis\PdfcrowdClient\Tests\Unit;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_MockObject_MockObject;
use Swis\PdfcrowdClient\Exceptions\PdfcrowdException;
use Swis\PdfcrowdClient\Http\RequestInterface;
use Swis\PdfcrowdClient\Pdfcrowd;
use Swis\PdfcrowdClient\Tests\Helpers\MockRequestFactory;

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

        $pdf = $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');

        $this->assertEquals($pdf, '123123');
    }

    public function testSuccessfulConvertUri()
    {
        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->once())
            ->method('setUrl')
            ->with('https://pdfcrowd.com/api/pdf/convert/uri/');

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $pdf = $this->pdfcrowd->convertUri('https://google.com');

        $this->assertEquals($pdf, '123123');
    }

    public function testSuccessfulConversionToOutputDestination()
    {
        $this->setRequestMocks(1);

        $outputFilename = __DIR__.'/../data/html_to_file.txt';
        @unlink($outputFilename);

        $output_destination = fopen($outputFilename, 'w');

        $this->requestMocks[0]->expects($this->once())
            ->method('setUrl')
            ->with('https://pdfcrowd.com/api/pdf/convert/html/');

        $this->requestMocks[0]->expects($this->once())
            ->method('setOutputDestination')
            ->with($output_destination);

        $this->requestMocks[0]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[0]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);

        $this->pdfcrowd->setOutputDestination($output_destination);

        $pdf = $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');

        $this->assertEquals($pdf, '123123');

        @unlink($outputFilename);
    }

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

        $tokens = $this->pdfcrowd->availableTokens();

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


        $pdf = $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');

        $this->assertEquals($pdf, '123123');
        $this->assertEquals($this->pdfcrowd->getUsedTokens(), 4);
    }

    public function testItTracksTokensBeforeAndAfterConvertUri()
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


        // second request should convert html

        $this->requestMocks[1]->expects($this->once())
            ->method('setUrl')
            ->with('https://pdfcrowd.com/api/pdf/convert/uri/');

        $this->requestMocks[1]->expects($this->once())
            ->method('execute')
            ->willReturn('123123');

        $this->requestMocks[1]->expects($this->once())
            ->method('getHttpStatusCode')
            ->willReturn(200);


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


        $pdf = $this->pdfcrowd->convertURI('https://google.com');

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

        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("myErorMessage");

        $this->pdfcrowd->convertHtml('<html><body><h1>Testing 123.</h1></body></html>');
    }

    public function testFailedConvertUriWithInvalidUri()
    {
        $this->setRequestMocks(1);

        $this->requestMocks[0]->expects($this->never())
            ->method('setUrl')
            ->with('https://pdfcrowd.com/api/pdf/convert/uri/');

        $this->expectException(PdfcrowdException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage("convertURI(): the URL must start with http:// or https:// (got 'google.com')");

        $this->pdfcrowd->convertUri('google.com');
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
            ['setHorizontalMargin', ['100'], 'margin_right', '100'],
            ['setHorizontalMargin', ['100'], 'margin_left', '100'],
            ['setVerticalMargin', ['100'], 'margin_top', '100'],
            ['setVerticalMargin', ['100'], 'margin_bottom', '100'],
            ['setBottomMargin', ['100'], 'margin_bottom', '100'],
            ['setPageMargins', ['100', '200', '300', '400'], 'margin_top', '100'],
            ['setPageMargins', ['100', '200', '300', '400'], 'margin_right', '200'],
            ['setPageMargins', ['100', '200', '300', '400'], 'margin_bottom', '300'],
            ['setPageMargins', ['100', '200', '300', '400'], 'margin_left', '400'],
            ['setEncrypted', [true], 'encrypted', true],
            ['setUserPassword', ['secret'], 'user_pwd', 'secret'],
            ['setOwnerPassword', ['secret321'], 'owner_pwd', 'secret321'],
            ['setNoPrint', [true], 'no_print', true],
            ['setNoModify', [true], 'no_modify', true],
            ['setNoCopy', [true], 'no_copy', true],
            ['setPageLayout', [Pdfcrowd::PAGE_LAYOUT_SINGLE_PAGE], 'page_layout', Pdfcrowd::PAGE_LAYOUT_SINGLE_PAGE],
            ['setPageLayout', [Pdfcrowd::PAGE_LAYOUT_CONTINUOUS], 'page_layout', Pdfcrowd::PAGE_LAYOUT_CONTINUOUS],
            ['setPageLayout', [Pdfcrowd::PAGE_LAYOUT_CONTINUOUS_FACING], 'page_layout', Pdfcrowd::PAGE_LAYOUT_CONTINUOUS_FACING],
            ['setPageMode', [Pdfcrowd::PAGE_MODE_NONE_VISIBLE], 'page_mode', Pdfcrowd::PAGE_MODE_NONE_VISIBLE],
            ['setPageMode', [Pdfcrowd::PAGE_MODE_THUMBNAILS_VISIBLE], 'page_mode', Pdfcrowd::PAGE_MODE_THUMBNAILS_VISIBLE],
            ['setPageMode', [Pdfcrowd::PAGE_MODE_FULLSCREEN], 'page_mode', Pdfcrowd::PAGE_MODE_FULLSCREEN],
            ['setFooterText', ['test123'], 'footer_text', 'test123'],
            ['enableImages', [false], 'no_images', true],
            ['enableBackgrounds', [false], 'no_backgrounds', true],
            ['setHtmlZoom', [true], 'html_zoom', true],
            ['enableJavaScript', [false], 'no_javascript', true],
            ['enableHyperlinks', [false], 'no_hyperlinks', true],
            ['setDefaultTextEncoding', ['my-encoding'], 'text_encoding', 'my-encoding'],
            ['usePrintMedia', [true], 'use_print_media', true],
            ['setMaxPages', [3], 'max_pages', 3],
            ['enablePdfcrowdLogo', [true], 'pdfcrowd_logo', true],
            ['setInitialPdfZoomType', [Pdfcrowd::INITIAL_PDF_ZOOM_TYPE_FIT_WIDTH], 'initial_pdf_zoom_type', Pdfcrowd::INITIAL_PDF_ZOOM_TYPE_FIT_WIDTH],
            ['setInitialPdfZoomType', [Pdfcrowd::INITIAL_PDF_ZOOM_TYPE_FIT_HEIGHT], 'initial_pdf_zoom_type', Pdfcrowd::INITIAL_PDF_ZOOM_TYPE_FIT_HEIGHT],
            ['setInitialPdfZoomType', [Pdfcrowd::INITIAL_PDF_ZOOM_TYPE_FIT_PAGE], 'initial_pdf_zoom_type', Pdfcrowd::INITIAL_PDF_ZOOM_TYPE_FIT_PAGE],
            ['setInitialPdfExactZoom', [123], 'initial_pdf_zoom_type', 4],
            ['setInitialPdfExactZoom', [123], 'initial_pdf_zoom', 123],
            ['setPdfScalingFactor', [1.23], 'pdf_scaling_factor', 1.23],
            ['setAuthor', ['AuthorName'], 'author', 'AuthorName'],
            ['setFailOnNon200', [true], 'fail_on_non200', true],
            ['setFooterHtml', ['test123'], 'footer_html', 'test123'],
            ['setFooterUrl', ['myUrl'], 'footer_url', 'myUrl'],
            ['setHeaderHtml', ['test123'], 'header_html', 'test123'],
            ['setHeaderUrl', ['myUrl'], 'header_url', 'myUrl'],
            ['setPageBackgroundColor', ['#123456'], 'page_background_color', '#123456'],
            ['setTransparentBackground', [true], 'transparent_background', true],
            ['setPageNumberingOffset', [4], 'page_numbering_offset', 4],
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

    public function testSetPageLayoutWithInvalidValue()
    {
        $this->expectException(PdfcrowdException::class);

        $this->expectExceptionMessage('Invalid page layout value!');

        $this->pdfcrowd->setPageLayout(123);
    }


    public function testSetPageModeWithInvalidValue()
    {
        $this->expectException(PdfcrowdException::class);

        $this->expectExceptionMessage('Invalid page mode value!');

        $this->pdfcrowd->setPageMode(123);
    }


    public function testSetInitialPdfZoomTypeWithInvalidValue()
    {
        $this->expectException(PdfcrowdException::class);

        $this->expectExceptionMessage('Invalid initial pdf zoom type value!');

        $this->pdfcrowd->setInitialPdfZoomType(123);
    }
}
