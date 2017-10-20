<?php

declare(strict_types=1);

namespace Swis\PdfcrowdClient\Http;

interface RequestInterface
{
    public function setOption($name, $value);

    public function setUserAgent(string $userAgentString);

    public function setTimeout(float $timeout);

    public function setVerifySsl(bool $verify);

    public function setProxy(string $proxy, int $port);

    public function setProxyAuth(string $username, string $password);

    public function setUrl(string $url);

    public function setPort(int $port);

    public function setBody(array $body);

    public function setOutputDestination($output_destination);

    public function execute();

    public function getHttpStatusCode(): int;

    public function getErrorMessage();

    public function getErrorNumber();

    public function close();
}
