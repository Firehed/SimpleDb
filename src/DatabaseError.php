<?php
declare(strict_types=1);

namespace Firehed\SimpleDb;

use Exception;
use Throwable;

class DatabaseError extends Exception
{
    private $sqlstate = '';

    public function __construct(
        string $message = '',
        int $code = 0,
        string $sqlstate = '',
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->sqlstate = $sqlstate;
    }

    public function getSqlState(): string
    {
        return $this->sqlstate;
    }
}
