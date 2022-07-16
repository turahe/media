<?php

namespace Turahe\Media\Exceptions;

use Exception;

class InvalidConversion extends Exception
{
    /**
     * @param string $name
     * @return InvalidConversion
     */
    public static function doesNotExist(string $name): InvalidConversion
    {
        return new static("Conversion `{$name}` does not exist");
    }
}
