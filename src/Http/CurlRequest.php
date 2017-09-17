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
    }

    public function setOption($name, $value)
    {
        curl_setopt($this->resource, $name, $value);
    }

    public function execute()
    {
        return curl_exec($this->resource);
    }

    public function getInfo($name)
    {
        return curl_getinfo($this->resource, $name);
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
