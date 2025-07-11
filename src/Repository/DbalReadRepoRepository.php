<?php

namespace App\Repository;

use App\Entity\Repo;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class DbalReadRepoRepository implements ReadRepoRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection
    )
    {}

    public function find(int $id): ?Repo
    {
        return $this->entityManager->find(Repo::class, $id);
    }

    public function exists(int $id): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM repo
            WHERE id = :id
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'id' => $id
        ]);

        return (bool) $result;
    }
}
