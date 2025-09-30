<?php

namespace App\DTO;

class ImportCommandOptions
{
    public function __construct(
        public readonly string $filePath,
        public readonly int $limit,
        public readonly bool $pushToSheets
    ) {}

    public static function fromInput(string $filePath, int $limit, bool $pushToSheets): self
    {
        return new self($filePath, $limit, $pushToSheets);
    }
}
