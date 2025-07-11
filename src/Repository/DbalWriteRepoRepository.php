<?php

namespace App\Repository;

use App\Entity\Repo;
use Doctrine\DBAL\Connection;

class DbalWriteRepoRepository implements WriteRepoRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function create(Repo $repo): void
    {
        $sql = <<<SQL
        INSERT INTO repo (id, name, url)
        VALUES (:id, :name, :url)
SQL;
        $this->connection->executeQuery($sql, [
            'id' => $repo->id(),
            'name' => $repo->name(),
            'url' => $repo->url(),
        ]);
    }
}
