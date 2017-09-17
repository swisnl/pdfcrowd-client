<?php

declare(strict_types=1);

namespace Swis\PdfcrowdClient\Http;

interface RequestInterface
{
    public function setOption($name, $value);

    public function execute();

    public function getInfo($name);

    public function getErrorMessage();

    public function getErrorNumber();

    public function close();
}
