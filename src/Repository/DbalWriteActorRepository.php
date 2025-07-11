<?php

namespace App\Repository;

use App\Entity\Actor;
use Doctrine\DBAL\Connection;

class DbalWriteActorRepository implements WriteActorRepository
{
    public function __construct(
        private readonly Connection $connection
    )
    {
    }

    public function create(Actor $actor): void
    {
        $sql = <<<SQL
        INSERT INTO actor (id, login, url, avatar_url)
        VALUES (:id, :login, :url, :avatar_url)
SQL;
        $this->connection->executeQuery($sql, [
            'id' => $actor->getId(),
            'login' => $actor->getLogin(),
            'url' => $actor->getUrl(),
            'avatar_url' => $actor->getAvatarUrl(),
        ]);
    }
}
