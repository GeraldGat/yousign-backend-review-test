<?php

namespace App\Repository;

use App\Dto\EventInput;
use Doctrine\DBAL\Connection;

class DbalWriteEventRepository implements WriteEventRepository
{
    public function __construct(
        private readonly Connection $connection
    )
    {
    }

    public function create(
        int $id,
        string $type,
        int $actorId,
        int $repoId,
        array $jsonPayload,
        \DateTimeImmutable $createAt
    ): void
    {
        $sql = <<<SQL
        INSERT INTO event (id, type, actor_id, repo_id, payload, create_at)
        VALUES (:id, :type, :actor_id, :repo_id, :payload, :create_at)
SQL;
        $this->connection->executeQuery($sql, [
            'id' => $id,
            'type' => $type,
            'actor_id' => $actorId,
            'repo_id' => $repoId,
            'payload' => json_encode($jsonPayload),
            'create_at' => $createAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function update(EventInput $authorInput, int $id): void
    {
        $sql = <<<SQL
        UPDATE event
        SET comment = :comment
        WHERE id = :id
SQL;

        $this->connection->executeQuery($sql, ['id' => $id, 'comment' => $authorInput->comment]);
    }
}
