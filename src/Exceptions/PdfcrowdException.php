<?php

namespace Swis\PdfcrowdClient\Exceptions;

class PdfcrowdException extends \Exception
{
    public function __toString()
    {
        if ($this->code) {
            return "[{$this->code}] {$this->message}\n";
        } else {
            return "{$this->message}\n";
        }
    }
}
