<?php

declare(strict_types=1);

namespace Swis\PdfcrowdClient\Tests;

use Swis\PdfcrowdClient\Http\FactoryInterface;

class MockRequestFactory implements FactoryInterface
{
    protected $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function create()
    {
        return reset($this->items);
    }
}
