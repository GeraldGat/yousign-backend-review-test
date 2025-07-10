<?php

namespace App\Repository;

use App\Entity\Actor;

interface WriteActorRepository
{
    public function create(Actor $actor): void;
}
