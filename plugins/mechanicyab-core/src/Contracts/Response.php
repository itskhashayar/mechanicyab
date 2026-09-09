<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

final class Response
{
    public function __construct(
        public readonly bool $success,
        public readonly mixed $data = null,
        public readonly array $meta = [],
        public readonly array $errors = [],
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'meta' => $this->meta,
            'errors' => $this->errors,
        ];
    }
}
