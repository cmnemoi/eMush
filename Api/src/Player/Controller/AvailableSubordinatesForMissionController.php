<?php

declare(strict_types=1);

namespace Mush\Player\Controller;

use Mush\Chat\Services\GetAvailableSubordinatesForMissionService;
use Mush\Game\Controller\AbstractGameController;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Voter\PlayerVoter;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

final class AvailableSubordinatesForMissionController extends AbstractGameController
{
    public function __construct(
        AdminServiceInterface $adminService,
        private readonly GetAvailableSubordinatesForMissionService $getAvailaibleSubordinatesForMission,
    ) {
        parent::__construct($adminService);
    }

    /**
     * Get availaible subordinates for mission.
     */
    #[Route('/player/{id}/availaible-subordinates', methods: ['GET'])]
    public function availaibleSubordinatesEndpoint(Player $player): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $this->getUserOrThrow());
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);

        return $this->json($this->getAvailaibleSubordinatesForMission->execute($player), Response::HTTP_OK);
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
