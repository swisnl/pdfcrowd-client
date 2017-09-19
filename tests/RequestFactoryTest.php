<?php

namespace Swis\PdfcrowdClient\Tests;

use Swis\PdfcrowdClient\Http\CurlRequest;
use Swis\PdfcrowdClient\Http\RequestFactory;

class RequestFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testRequestFactoryReturnsCurlRequestObject()
    {
        $factory = new RequestFactory();

        $requestObject = $factory->create();

        $this->assertInstanceOf(CurlRequest::class, $requestObject);
    }
}
