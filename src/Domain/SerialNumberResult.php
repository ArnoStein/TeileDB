<?php
declare(strict_types=1);

namespace App\Domain;

class SerialNumberResult
{
    public bool $ok;
    public ?string $canonical;
    public ?string $errorCode;
    public ?string $errorMessage;

    private function __construct(bool $ok, ?string $canonical, ?string $errorCode, ?string $errorMessage)
    {
        $this->ok = $ok;
        $this->canonical = $canonical;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    public static function success(string $canonical): self
    {
        return new self(true, $canonical, null, null);
    }

    public static function fail(string $errorCode, string $errorMessage): self
    {
        return new self(false, null, $errorCode, $errorMessage);
    }
}
