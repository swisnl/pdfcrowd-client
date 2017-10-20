<?php

namespace Swis\PdfcrowdClient\Tests\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Swis\PdfcrowdClient\Http\GuzzleRequest;
use Swis\PdfcrowdClient\Http\RequestInterface;
use Swis\PdfcrowdClient\Tests\Unit\BaseTestCase;

class GuzzleRequestTest extends BaseTestCase
{
    /** @var Client|\PHPUnit_Framework_MockObject_MockObject $mockClient */
    protected $mockClient;

    /** @var Response|\PHPUnit_Framework_MockObject_MockObject $mockResponse */
    protected $mockResponse;

    /** @var GuzzleRequest */
    protected $request;

    public function setUp()
    {
        parent::setUp();

        $this->mockClient = $this->getMockBuilder(Client::class)->getMock();
        ;

        $this->mockResponse = $this->getMockBuilder(Response::class)->getMock();

        $this->request = new GuzzleRequest();
        $this->request->setClient($this->mockClient);
    }
    
    protected function buildExpectedRequestOptions($mergeOptions = []): array
    {
        $expectedRequestOptions = [
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::HTTP_ERRORS => false,
        ];

        return array_merge($expectedRequestOptions, $mergeOptions);
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::__construct()
     */
    public function testItInstantiatesARequestInterface()
    {
        $request = new GuzzleRequest();

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(GuzzleRequest::class, $request);
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::__construct()
     */
    public function testItSetsDefaultRequestOptions()
    {
        $expectedRequestOptions = $this->buildExpectedRequestOptions();

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->request->execute();
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::setClient()
     */
    public function testSetClient()
    {
        $userAgentString = 'myUserAgent';

        $expectedRequestOptions = $this->buildExpectedRequestOptions([
            RequestOptions::HEADERS => [
                'User-Agent' => $userAgentString,
            ],
        ]);

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->request->setUserAgent($userAgentString);
        $this->request->execute();
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::setUserAgent()
     */
    public function testSetUserAgent()
    {
        $userAgentString = 'myUserAgent';

        $expectedRequestOptions = $this->buildExpectedRequestOptions([
            RequestOptions::HEADERS => [
                'User-Agent' => $userAgentString,
            ],
        ]);

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->request->setUserAgent($userAgentString);
        $this->request->execute();
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::setTimeout()
     */
    public function testSetTimeout()
    {
        $timeout = 123.45;

        $expectedRequestOptions = $this->buildExpectedRequestOptions([
            RequestOptions::TIMEOUT => $timeout,
        ]);

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->request->setTimeout($timeout);
        $this->request->execute();
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::setVerifySsl()
     */
    public function testSetVerifySsl()
    {
        $expectedRequestOptions = $this->buildExpectedRequestOptions([
            RequestOptions::VERIFY => true,
        ]);

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->request->setVerifySsl(true);
        $this->request->execute();
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::setProxy()
     */
    public function testSetProxy()
    {
        $proxy = 'myProxy';
        $port = 132;

        $expectedRequestOptions = $this->buildExpectedRequestOptions([
            RequestOptions::PROXY => $proxy.':'.$port,
        ]);

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->request->setProxy($proxy, $port);
        $this->request->execute();
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::setProxyAuth()
     */
    public function testSetProxyAuth()
    {
        $username = 'myUsername';
        $password = 'myPassword';

        $expectedRequestOptions = $this->buildExpectedRequestOptions([
            RequestOptions::AUTH => [
                $username,
                $password,
            ],
        ]);

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->request->setProxyAuth($username, $password);
        $this->request->execute();
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::setUrl()
     */
    public function testSetUrl()
    {
        $url = 'myUrl';

        $expectedRequestOptions = $this->buildExpectedRequestOptions();

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', $url, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->request->setUrl($url);
        $this->request->execute();
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::setBody()
     */
    public function testSetBody()
    {
        $body = [
            'foo' => 'bar',
            'baz',
        ];

        $expectedRequestOptions = $this->buildExpectedRequestOptions([
            RequestOptions::FORM_PARAMS => $body,
        ]);

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->request->setBody($body);
        $this->request->execute();
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::setOutputDestination()
     */
    public function testSetOutputDestination()
    {
        $filename = __DIR__.'/../../data/dummy.out';
        @unlink($filename);

        $destination = fopen($filename, 'w');

        $expectedRequestOptions = $this->buildExpectedRequestOptions([
            RequestOptions::SINK => $destination,
        ]);

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->request->setOutputDestination($destination);
        $this->request->execute();

        @unlink($filename);
    }

    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::execute()
     */
    public function testExecute()
    {
        $expectedRequestOptions = $this->buildExpectedRequestOptions();

        $expectedResponse = 'responseBody';

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->mockResponse->expects(self::once())
            ->method('getBody')
            ->willReturn($expectedResponse);

        $response = $this->request->execute();

        $this->assertEquals($expectedResponse, $response);
    }


    /**
     * @covers \Swis\PdfcrowdClient\Http\GuzzleRequest::getHttpStatusCode()
     */
    public function testGetHttpStatusCode()
    {
        $expectedRequestOptions = $this->buildExpectedRequestOptions();

        $expectedStatusCode = 123;

        $this->mockClient->expects(self::once())
            ->method('request')
            ->with('POST', null, $expectedRequestOptions)
            ->willReturn($this->mockResponse);

        $this->mockResponse->expects(self::once())
            ->method('getStatusCode')
            ->willReturn($expectedStatusCode);

        $this->request->execute();
        $statusCode = $this->request->getHttpStatusCode();

        $this->assertEquals($expectedStatusCode, $statusCode);
    }
}
