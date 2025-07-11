<?php

namespace App\Repository;

use App\Dto\EventInput;
use App\Entity\Event;

interface WriteEventRepository
{
    public function create(
        int $id,
        string $type,
        int $actorId,
        int $repoId,
        array $payload,
        \DateTimeImmutable $createAt,
    ): void;
    public function update(EventInput $authorInput, int $id): void;
}
