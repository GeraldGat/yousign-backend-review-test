<?php

namespace App\Controller;

use App\Dto\SearchInput;
use App\Repository\ReadEventRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SearchController
{
    public function __construct(
        private readonly ReadEventRepository $repository,
        private readonly DenormalizerInterface $denormalizer
    )
    {
    }

    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function searchCommits(Request $request): JsonResponse
    {
        /** @var EventInput $eventInput */
        $searchInput = $this->denormalizer->denormalize($request->query->all(), SearchInput::class);

        $countByType = $this->repository->countByType($searchInput);

        $data = [
            'meta' => [
                'totalEvents' => $this->repository->countAll($searchInput),
                'totalPullRequests' => $countByType['PullRequestEvent'] ?? 0,
                'totalCommits' => $countByType['PushEvent'] ?? 0,
                'totalComments' => $countByType['CommitCommentEvent'] ?? 0
            ],
            'data' => [
                'events' => $this->repository->getLatest($searchInput),
                'stats' => $this->repository->statsByTypePerHour($searchInput)
            ]
        ];

        return new JsonResponse($data);
    }
}
