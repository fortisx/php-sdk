<?php

declare(strict_types=1);

namespace FortisX\SDK;

class APIError extends \Exception
{
    public int $status;

    public array $details;

    public function __construct(string $message, int $status = 0, array $details = [])
    {
        parent::__construct($message);

        $this->status = $status;
        $this->details = $details;
    }
}
