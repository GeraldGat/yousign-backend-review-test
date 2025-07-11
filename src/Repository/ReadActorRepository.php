<?php

namespace App\Repository;

use App\Entity\Actor;

interface ReadActorRepository
{
    public function find(int $id): ?Actor;
    public function exists(int $id): bool;
}
