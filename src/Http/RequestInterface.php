<?php

declare(strict_types=1);

namespace Swis\PdfcrowdClient\Http;

use GuzzleHttp\Client;

interface RequestInterface
{
    public function setClient(Client $client);

    public function setUserAgent(string $userAgentString);

    public function setTimeout(float $timeout);

    public function setProxy(string $proxy, int $port);

    public function setProxyAuth(string $username, string $password);

    public function setUrl(string $url);

    public function setBody(array $body);

    public function setOutputDestination($output_destination);

    public function execute();

    public function getHttpStatusCode(): int;
}
