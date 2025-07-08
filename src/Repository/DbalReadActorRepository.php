<?php

namespace App\Repository;

use App\Entity\Actor;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class DbalReadActorRepository implements ReadActorRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Connection $connection,
    )
    {}

    public function find(int $id): ?Actor
    {
        return $this->entityManager->find(Actor::class, $id);
    }

    public function exist(int $id): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM actor
            WHERE id = :id
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'id' => $id
        ]);

        return (bool) $result;
    }
}
