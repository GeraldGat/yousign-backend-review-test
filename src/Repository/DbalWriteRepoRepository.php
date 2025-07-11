<?php

namespace App\Repository;

use App\Entity\Repo;
use Doctrine\DBAL\Connection;

class DbalWriteRepoRepository implements WriteRepoRepository
{
    public function __construct(
        private readonly Connection $connection
    )
    {
    }

    public function create(Repo $repo): void
    {
        $sql = <<<SQL
        INSERT INTO repo (id, name, url)
        VALUES (:id, :name, :url)
SQL;
        $this->connection->executeQuery($sql, [
            'id' => $repo->getId(),
            'name' => $repo->getName(),
            'url' => $repo->getUrl(),
        ]);
    }
}
