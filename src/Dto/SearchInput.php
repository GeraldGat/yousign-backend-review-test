<?php

namespace App\Dto;

class SearchInput
{
    public function __construct(
        public \DateTimeImmutable $date,
        public string $keyword
    )
    {
    }
}
