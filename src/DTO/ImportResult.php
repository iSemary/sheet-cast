<?php

namespace App\DTO;

class ImportResult
{
    public function __construct(
        public readonly bool $success,
        public readonly array $data,
        public readonly int $recordsProcessed,
        public readonly int $recordsDisplayed,
        public readonly bool $pushedToSheets,
        public readonly ?string $error = null
    ) {}

    public static function success(
        array $data,
        int $recordsProcessed,
        int $recordsDisplayed,
        bool $pushedToSheets
    ): self {
        return new self(
            success: true,
            data: $data,
            recordsProcessed: $recordsProcessed,
            recordsDisplayed: $recordsDisplayed,
            pushedToSheets: $pushedToSheets
        );
    }

    public static function failure(string $error): self
    {
        return new self(
            success: false,
            data: [],
            recordsProcessed: 0,
            recordsDisplayed: 0,
            pushedToSheets: false,
            error: $error
        );
    }
}
