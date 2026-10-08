<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a JWT is malformed, has a wrong signature, is expired or has the wrong type.
 */
class InvalidTokenException extends RuntimeException
{
    //
}
