<?php

declare(strict_types=1);

namespace Mush\Achievement\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use Mush\Achievement\Query\GetUserAchievementsQuery;
use Mush\Achievement\Query\GetUserStatisticsQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[OA\Tag(name: 'Achievement')]
#[AsController]
final class AchievementController extends AbstractController
{
    public function __construct(private MessageBusInterface $queryBus) {}

    #[Get(path: '/statistics')]
    public function getUserStatisticsEndpoint(#[MapQueryString] GetUserStatisticsQuery $query): JsonResponse
    {
        $statistics = $this->queryBus->dispatch($query)->last(HandledStamp::class)?->getResult();

        return $this->json($statistics, status: Response::HTTP_OK, context: $query->toNormalizationContext());
    }

    #[Get(path: '/achievements')]
    public function getUserAchievementsEndpoint(#[MapQueryString] GetUserAchievementsQuery $query): JsonResponse
    {
        $achievements = $this->queryBus->dispatch($query)->last(HandledStamp::class)?->getResult();

        return $this->json($achievements, status: Response::HTTP_OK, context: $query->toNormalizationContext());
    }
}
