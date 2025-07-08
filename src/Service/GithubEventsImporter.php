<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\Repository\ReadActorRepository;
use App\Repository\ReadEventRepository;
use App\Repository\ReadRepoRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class GithubEventsImporter
{
    private int $cacheMaxSize = 500;
    private array $actorCache = [];
    private array $repoCache = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReadEventRepository $readEventRepository,
        private readonly ReadActorRepository $readActorRepository,
        private readonly ReadRepoRepository $readRepoRepository,
        private readonly LoggerInterface $logger,
    )
    {
        ini_set('memory_limit', '1G');
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
                    if (count($this->actorCache) + count($this->repoCache) > $this->cacheMaxSize) {
                        $this->clearCache();
                    }

                    $actor = $this->getOrCreateActor($eventData['actor']);
                    $repo = $this->getOrCreateRepo($eventData['repo']);

                    $eventId = (int) $eventData['id'];
                    
                    if($this->readEventRepository->exist($eventId)) {
                        unset($eventData, $actor, $repo);
                        continue;
                    }

                    $event = new Event(
                        $eventId,
                        $eventData['type'],
                        $actor,
                        $repo,
                        (array) $eventData['payload'],
                        \DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601_EXPANDED, $eventData['created_at'])
                    );

                    $this->entityManager->persist($event);
                    $batchIndex++;

                    unset($eventData, $event, $actor, $repo);

                    if($batchIndex % $batchSize === 0) {
                        $this->flushBatch($onProgress);
                        $batchIndex = 0;
                    }
                } catch(Throwable $e) {
                    $this->logger->error("Failed to import event at line $currentLine: {$e->getMessage()}");
                    unset($eventData);
                    continue;
                }
            }

            if($batchIndex > 0) {
                $this->flushBatch($onProgress);
            }
        } finally {
            gzclose($stream);
        }
    }

    private function getOrCreateActor(array $actorData): Actor
    {
        $actorId = $actorData['id'];
        
        if(!array_key_exists($actorId, $this->actorCache)) {
            $actor = $this->readActorRepository->find($actorId);
            if($actor === null) {
                $actor = Actor::fromArray($actorData);
                $this->entityManager->persist($actor);
            }
            $this->actorCache[$actorId] = $actor;
        }
        
        return $this->actorCache[$actorId];
    }

    private function getOrCreateRepo(array $repoData): Repo
    {
        $repoId = $repoData['id'];
        
        if(!array_key_exists($repoId, $this->repoCache)) {
            $repo = $this->readRepoRepository->find($repoId);
            if($repo === null) {
                $repo = Repo::fromArray($repoData);
                $this->entityManager->persist($repo);
            }
            $this->repoCache[$repoId] = $repo;
        }
        
        return $this->repoCache[$repoId];
    }

    private function flushBatch(?callable $onProgress = null): void
    {
        try {
            $this->entityManager->flush();
        } catch (Throwable $e) {
            $this->logger->error("Failed to flush batch: {$e->getMessage()}");
            throw $e;
        }
        
        $this->clearCache();
        $this->entityManager->clear();
        
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        if($onProgress !== null) {
            $onProgress();
        }
    }

    private function clearCache(): void
    {
        unset($this->actorCache, $this->repoCache);
        $this->actorCache = [];
        $this->repoCache = [];
    }
}