<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ImportGithubEventsCommandInput
{
    public function __construct(
        #[Assert\Date(message: 'Please use a valid date format (Y-m-d).')]
        #[Assert\NotNull()]
        public string $date,

        #[Assert\NotNull()]
        #[Assert\Range(min: 0, max: 23, notInRangeMessage: 'Hour must be between {{min}} and {{max}}.')]
        public int $hour
    )
    {
    }
}