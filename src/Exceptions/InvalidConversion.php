<?php

namespace Turahe\Media\Exceptions;

use Exception;

class InvalidConversion extends Exception
{
    public static function doesNotExist(string $name): InvalidConversion
    {
        return new static("Conversion `{$name}` does not exist");
    }
}
