<?php

declare(strict_types=1);

namespace Swis\PdfcrowdClient\Http;

use Swis\PdfcrowdClient\Exceptions\PdfcrowdException;

class CurlRequest implements RequestInterface
{
    private $resource;

    public function __construct()
    {
        if (!function_exists('curl_init')) {
            throw new PdfcrowdException('Curl php extension missing');
        }

        $this->resource = curl_init();

        curl_setopt($this->resource, CURLOPT_HEADER, false);
        curl_setopt($this->resource, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->resource, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->resource, CURLOPT_POST, true);
        curl_setopt($this->resource, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
    }

    public function setOption($name, $value)
    {
        curl_setopt($this->resource, $name, $value);
    }

    public function setUserAgent(string $userAgentString)
    {
        curl_setopt($this->resource, CURLOPT_USERAGENT, $userAgentString);
    }

    public function setTimeout(float $timeout)
    {
        curl_setopt($this->resource, CURLOPT_TIMEOUT, $timeout);
    }

    public function setVerifySsl(bool $verify)
    {
        curl_setopt($this->resource, CURLOPT_SSL_VERIFYPEER, $verify);
    }

    public function setProxy(string $proxy, int $port)
    {
        curl_setopt($this->resource, CURLOPT_PROXY, $proxy.':'.$port);
    }

    public function setProxyAuth(string $username, string $password)
    {
        curl_setopt($this->resource, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        curl_setopt($this->resource, CURLOPT_PROXYUSERPWD, $username.':'.$password);
    }

    public function setUrl(string $url)
    {
        curl_setopt($this->resource, CURLOPT_URL, $url);
    }

    public function setPort(int $port)
    {
        curl_setopt($this->resource, CURLOPT_PORT, $port);
    }

    public function setBody(array $body)
    {
        curl_setopt($this->resource, CURLOPT_POSTFIELDS, $body);
    }

    public function execute()
    {
        return curl_exec($this->resource);
    }

    public function getHttpStatusCode(): int
    {
        return (int) curl_getinfo($this->resource, CURLINFO_HTTP_CODE);
    }

    public function getErrorMessage()
    {
        return curl_error($this->resource);
    }

    public function getErrorNumber()
    {
        return curl_errno($this->resource);
    }

    public function close()
    {
        curl_close($this->resource);
    }
}
