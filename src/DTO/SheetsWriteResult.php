<?php

namespace App\DTO;

class SheetsWriteResult
{
    public function __construct(
        public readonly bool $success,
        public readonly int $updatedCells,
        public readonly int $updatedRows,
        public readonly int $updatedColumns,
        public readonly ?string $error = null
    ) {}

    public static function success(int $updatedCells, int $updatedRows, int $updatedColumns): self
    {
        return new self(
            success: true,
            updatedCells: $updatedCells,
            updatedRows: $updatedRows,
            updatedColumns: $updatedColumns
        );
    }

    public static function failure(string $error): self
    {
        return new self(
            success: false,
            updatedCells: 0,
            updatedRows: 0,
            updatedColumns: 0,
            error: $error
        );
    }
}
