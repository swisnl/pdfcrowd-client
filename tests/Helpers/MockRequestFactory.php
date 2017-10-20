<?php

declare(strict_types=1);

namespace Swis\PdfcrowdClient\Tests\Helpers;

use Swis\PdfcrowdClient\Http\FactoryInterface;
use Swis\PdfcrowdClient\Http\RequestInterface;

class MockRequestFactory implements FactoryInterface
{
    protected $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function create(): RequestInterface
    {
        return array_shift($this->items);
    }
}
