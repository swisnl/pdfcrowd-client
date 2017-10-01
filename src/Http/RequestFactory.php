<?php

declare(strict_types=1);

namespace Swis\PdfcrowdClient\Http;

class RequestFactory implements FactoryInterface
{
    public function create(): RequestInterface
    {
        return new GuzzleRequest();
    }
}
