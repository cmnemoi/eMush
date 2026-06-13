<?php

declare(strict_types=1);

namespace Mush\Player\Controller;

use Mush\Game\Controller\AbstractGameController;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\UseCase\MarkMissionAsReadUseCase;
use Mush\Player\UseCase\ToggleMissionCompletionUseCase;
use Mush\Player\Voter\PlayerVoter;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

final class CommanderMissionController extends AbstractGameController
{
    public function __construct(
        AdminServiceInterface $adminService,
        private readonly MarkMissionAsReadUseCase $markMissionAsRead,
        private readonly ToggleMissionCompletionUseCase $toggleMissionCompletion,
    ) {
        parent::__construct($adminService);
    }

    /**
     * Toggle mission completion.
     */
    #[OA\Tag(name: 'Player')]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The mission id',
        schema: new OA\Schema(type: 'integer')
    )]
    #[Route('/player/toggle-mission-completion/{id}', methods: ['PUT'])]
    public function toggleMissionCompletionEndpoint(CommanderMission $mission): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $this->getUserOrThrow());
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $mission->getSubordinate());

        $this->toggleMissionCompletion->execute($mission);

        return $this->json($mission, Response::HTTP_OK);
    }

    #[OA\Tag(name: 'Player')]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The mission id',
        schema: new OA\Schema(type: 'integer')
    )]
    #[Route('/player/mark-mission-as-read/{id}', methods: ['PUT'])]
    public function markMissionAsReadEndpoint(CommanderMission $mission): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $this->getUserOrThrow());
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $mission->getSubordinate());

        $this->markMissionAsRead->execute($mission);

        return $this->json($mission, Response::HTTP_OK);
    }

    private function getUserOrThrow(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'You should be logged in to access this endpoint.');
        }

        return $user;
    }
}
