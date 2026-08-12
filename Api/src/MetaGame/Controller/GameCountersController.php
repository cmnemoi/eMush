<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\MetaGame\Query\CachedGetGameCountersQueryHandler;
use Mush\MetaGame\Query\GetGameCountersQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'GameCounters')]
final class GameCountersController extends AbstractController
{
    public function __construct(private CachedGetGameCountersQueryHandler $queryHandler) {}

    #[Route('/game-counters', methods: ['GET'])]
    public function getGameCountersEndpoint(Request $request): JsonResponse
    {
        $counters = $this->queryHandler->execute(new GetGameCountersQuery());

        return $this->withCache($this->json($counters->toArray()), $request);
    }

    private function withCache(JsonResponse $response, Request $request): JsonResponse
    {
        $response->setEtag(hash('sha256', (string) $response->getContent()));
        $response->setCache([
            'public' => true,
            'no_cache' => true,
        ]);
        $response->isNotModified($request);

        return $response;
    }
}
