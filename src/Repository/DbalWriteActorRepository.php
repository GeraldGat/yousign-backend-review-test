<?php

namespace App\Repository;

use App\Entity\Actor;
use Doctrine\DBAL\Connection;

class DbalWriteActorRepository implements WriteActorRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function create(Actor $actor): void
    {
        $sql = <<<SQL
        INSERT INTO actor (id, login, url, avatar_url)
        VALUES (:id, :login, :url, :avatar_url)
SQL;
        $this->connection->executeQuery($sql, [
            'id' => $actor->id(),
            'login' => $actor->login(),
            'url' => $actor->url(),
            'avatar_url' => $actor->avatarUrl(),
        ]);
    }
}
