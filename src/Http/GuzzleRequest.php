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

    /** @var string */
    protected $url;

    public function __construct()
    {
        $this->client = new Client();

        $this->requestOptions[RequestOptions::CONNECT_TIMEOUT] = 10;
        $this->requestOptions[RequestOptions::HTTP_ERRORS] = false;
        $this->requestOptions[RequestOptions::VERIFY] = true;
    }

    /**
     * Override the client, used for testing purposes.
     *
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
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

    public function setBody(array $body)
    {
        $this->requestOptions[RequestOptions::FORM_PARAMS] = $body;
    }

    public function setOutputDestination($output_destination)
    {
        $this->requestOptions[RequestOptions::SINK] = $output_destination;
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
}
