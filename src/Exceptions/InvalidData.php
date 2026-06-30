<?php

namespace Foutraz\Withings\Exceptions;

use Exception;
use Throwable;

class InvalidData extends Exception
{
    /**
     * @var array<string, mixed>|array<int, mixed>|null
     */
    public ?array $data;

    public function __construct(?array $data = null, int $code = 0, ?Throwable $previous = null)
    {
        $this->data = $data;

        $message = $data !== null
            ? (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : 'Invalid data.';

        parent::__construct($message, $code, $previous);
    }
}
