<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A rule of the auction or the escrow was broken (bid too low, not enough funds, wrong state...).
 * The message is meant for the user. Anything else that goes wrong is a server error and its
 * message is never shown.
 */
class EscrowException extends RuntimeException
{
}
