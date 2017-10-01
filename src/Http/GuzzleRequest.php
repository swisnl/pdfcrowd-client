<?php

declare(strict_types=1);

namespace Swis\PdfcrowdClient\Http;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class GuzzleRequest implements RequestInterface
{
    /** @var \GuzzleHttp\Client  */
    protected $client;

    /** @var \GuzzleHttp\Psr7\Request  */
    protected $request;

    /** @var array  */
    protected $requestOptions = [];

    /** @var \GuzzleHttp\Psr7\Response */
    protected $response;

    /** @var int */
    protected $port;

    /** @var string */
    protected $url;

    public function __construct()
    {
        $this->client = new Client();

        $this->requestOptions[RequestOptions::CONNECT_TIMEOUT] = 10;
        $this->requestOptions[RequestOptions::HTTP_ERRORS] = false;
    }

    public function setOption($name, $value)
    {
        // todo: implement
    }

    public function setUserAgent(string $userAgentString)
    {
        if (!isset($this->requestOptions[RequestOptions::HEADERS])) {
            $this->requestOptions[RequestOptions::HEADERS] = [];
        }

        $this->requestOptions[RequestOptions::HEADERS]['User-Agent'] = $userAgentString;
    }

    public function setTimeout(float $timeout)
    {
        $this->requestOptions[RequestOptions::TIMEOUT] = $timeout;
    }

    public function setVerifySsl(bool $verify)
    {
        $this->requestOptions[RequestOptions::VERIFY] = $verify;
    }

    public function setProxy(string $proxy, int $port)
    {
        $this->requestOptions[RequestOptions::PROXY] = $proxy.':'.$port;
    }

    public function setProxyAuth(string $username, string $password)
    {
        $this->requestOptions[RequestOptions::AUTH] = [
            $username,
            $password,
        ];
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function setPort(int $port)
    {
        $this->port = $port;
    }

    public function setBody(array $body)
    {
        $this->requestOptions[RequestOptions::FORM_PARAMS] = $body;
    }

    public function execute()
    {
        $this->response = $this->client->request('POST', $this->url, $this->requestOptions);

        return $this->response->getBody();
    }

    public function getHttpStatusCode(): int
    {
        return (int) $this->response->getStatusCode();
    }

    public function getErrorMessage()
    {
        try {
            return (string) $this->response;
        } catch (\Exception $e) {
            return 'Unknown error.';
        }
    }

    public function getErrorNumber()
    {
        return (int) $this->response->getStatusCode();
    }

    public function close()
    {
        // todo: this method should be deleted once we our done with Curl
    }
}
