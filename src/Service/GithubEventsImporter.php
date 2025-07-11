<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Actor;
use App\Entity\Repo;
use App\Repository\ReadActorRepository;
use App\Repository\ReadEventRepository;
use App\Repository\ReadRepoRepository;
use App\Repository\WriteActorRepository;
use App\Repository\WriteEventRepository;
use App\Repository\WriteRepoRepository;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class GithubEventsImporter
{
    private int $cacheMaxSize = 500;
    private array $actorIdCache = [];
    private array $repoIdCache = [];

    public function __construct(
        private readonly ReadEventRepository $readEventRepository,
        private readonly ReadActorRepository $readActorRepository,
        private readonly ReadRepoRepository $readRepoRepository,
        private readonly WriteEventRepository $writeEventRepository,
        private readonly WriteActorRepository $writeActorRepository,
        private readonly WriteRepoRepository $writeRepoRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function importFromFile(string $source, int $batchSize = 1000, ?callable $onProgress = null): void
    {
        $stream = @gzopen($source, 'r');
        if($stream == false) {
            throw new RuntimeException("Could not open specified source file '$source'.");
        }

        $batchIndex = 0;
        $currentLine = 0;

        try {
            while(gzeof($stream) == false) {
                $line = gzgets($stream);
                $currentLine++;
                if(empty($line = trim($line))) {
                    continue;
                }

                $eventData = json_decode($line, true);
                unset($line);
                
                if($eventData === null && json_last_error() !== JSON_ERROR_NONE) {
                    $this->logger->warning("Invalid JSON at line $currentLine in '$source'.");
                    continue;
                }

                try {
                    if (count($this->actorIdCache) + count($this->repoIdCache) > $this->cacheMaxSize) {
                        $this->clearCache();
                    }

                    $actorId = $this->createIfActorDontExist($eventData['actor']);
                    $repoId = $this->createIfRepoDontExist($eventData['repo']);

                    $eventId = (int) $eventData['id'];
                    
                    if($this->readEventRepository->exist($eventId)) {
                        unset($eventData, $eventId, $actorId, $repoId);
                        continue;
                    }

                    $this->writeEventRepository->create(
                        $eventId,
                        $eventData['type'],
                        $actorId,
                        $repoId,
                        (array) $eventData['payload'],
                        \DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601_EXPANDED, $eventData['created_at'])
                    );
                    $batchIndex++;

                    unset($eventData, $eventId, $actorId, $repoId, $event);

                    if($batchIndex % $batchSize === 0) {
                        $this->clear($onProgress);
                    }
                } catch(Throwable $e) {
                    $this->logger->error("Failed to import event at line $currentLine: {$e->getMessage()}");
                    unset($eventData);
                    continue;
                }
            }

            if($batchIndex > 0) {
                $this->clear($onProgress);
            }
        } finally {
            gzclose($stream);
        }
    }

    private function createIfActorDontExist(array $actorData): int
    {
        $actorId = (int) $actorData['id'];
        
        if(!array_key_exists($actorId, $this->actorIdCache)) {
            $actorExist = $this->readActorRepository->exist($actorId);
            if($actorExist === false) {
                $actor = Actor::fromArray($actorData);
                $this->writeActorRepository->create($actor);
            }
            $this->actorIdCache[$actorId] = $actorId;
        }
        
        return $actorId;
    }

    private function createIfRepoDontExist(array $repoData): int
    {
        $repoId = (int) $repoData['id'];
        
        if(!array_key_exists($repoId, $this->repoIdCache)) {
            $repoExist = $this->readRepoRepository->exist($repoId);
            if($repoExist === false) {
                $repo = Repo::fromArray($repoData);
                $this->writeRepoRepository->create($repo);
            }
            $this->repoIdCache[$repoId] = $repoId;
        }
        
        return $this->repoIdCache[$repoId];
    }

    private function clear(?callable $onProgress = null): void
    {
        $this->clearCache();
        
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        if($onProgress !== null) {
            $onProgress();
        }
    }

    private function clearCache(): void
    {
        unset($this->actorIdCache, $this->repoIdCache);
        $this->actorIdCache = [];
        $this->repoIdCache = [];
    }
}