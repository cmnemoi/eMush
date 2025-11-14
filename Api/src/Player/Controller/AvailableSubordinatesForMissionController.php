<?php

declare(strict_types=1);

namespace Mush\Player\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Chat\Services\GetAvailableSubordinatesForMissionService;
use Mush\Game\Controller\AbstractGameController;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Voter\PlayerVoter;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     *
     * @OA\Tag(name="Player")
     *
     * * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The player id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/player/{id}/availaible-subordinates")
     */
    public function availaibleSubordinatesEndpoint(Player $player): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $this->getUserOrThrow());
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);

        return $this->view($this->getAvailaibleSubordinatesForMission->execute($player), Response::HTTP_OK);
    }

    private function getUserOrThrow(): User
    {
        return $this->getUser() instanceof User ? $this->getUser() : throw new HttpException(Response::HTTP_UNAUTHORIZED, 'You should be logged in to access this endpoint.');
    }
}
