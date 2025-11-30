<?php

declare(strict_types=1);

namespace Mush\Achievement\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Mush\Achievement\Command\UpdateUserStatisticCommand;
use Mush\Achievement\Query\GetUserAchievementsQuery;
use Mush\Achievement\Query\GetUserStatisticsQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Achievement')]
#[AsController]
final class AchievementController extends AbstractController
{
    public function __construct(private MessageBusInterface $queryBus, private MessageBusInterface $commandBus) {}

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

    #[Post(path: '/statistics/update')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function incrementUserAchievementEndpoint(#[MapRequestPayload] UpdateUserStatisticCommand $command): JsonResponse
    {
        $this->commandBus->dispatch($command);

        return $this->json(
            data: ['message' => "Statistic {$command->statisticName->value} updated successfully for user {$command->userId} to {$command->count}"],
            status: Response::HTTP_OK
        );
    }
}
