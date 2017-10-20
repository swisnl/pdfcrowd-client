<?php

namespace Swis\PdfcrowdClient\Tests\Http;

use Swis\PdfcrowdClient\Http\RequestFactory;
use Swis\PdfcrowdClient\Http\RequestInterface;

class RequestFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testRequestFactoryReturnsRequestInterface()
    {
        $factory = new RequestFactory();

        $requestObject = $factory->create();

        $this->assertInstanceOf(RequestInterface::class, $requestObject);
    }
}
