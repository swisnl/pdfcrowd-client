<?php

namespace Swis\PdfcrowdClient\Tests;

use Swis\PdfcrowdClient\Http\RequestFactory;
use Swis\PdfcrowdClient\Http\RequestInterface;

class RequestFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testRequestFactoryReturnsCurlRequestObject()
    {
        $factory = new RequestFactory();

        $requestObject = $factory->create();

        $this->assertInstanceOf(RequestInterface::class, $requestObject);
    }
}
