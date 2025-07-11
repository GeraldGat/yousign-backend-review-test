<?php

namespace App\Repository;

use App\Entity\Repo;

interface ReadRepoRepository
{
    public function find(int $id): ?Repo;
    public function exists(int $id): bool;
}
