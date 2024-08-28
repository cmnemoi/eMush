<?php

declare(strict_types=1);

namespace Mush\Player\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Game\Controller\AbstractGameController;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\UseCase\ToggleMissionCompletionUseCase;
use Mush\Player\Voter\PlayerVoter;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ToggleMissionCompletionController extends AbstractGameController
{
    public function __construct(
        AdminServiceInterface $adminService,
        private readonly ToggleMissionCompletionUseCase $toggleMissionCompletion,
    ) {
        parent::__construct($adminService);
    }

    /**
     * Toggle mission completion.
     *
     * @OA\Tag(name="Player")
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The player id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *    name="id",
     *    in="path",
     *    description="The mission id",
     *
     *   @OA\Schema(type="integer")
     * )
     *
     * @Security(name="Bearer")
     *
     * @Rest\Put(path="/player/toggle-mission-completion/{id}")
     */
    public function toggleMissionCompletionEndpoint(CommanderMission $mission): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $this->getUserOrThrow());
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $mission->getSubordinate());

        $this->toggleMissionCompletion->execute($mission);

        return $this->view($mission, Response::HTTP_OK);
    }

    private function getUserOrThrow(): User
    {
        return $this->getUser() instanceof User ? $this->getUser() : throw new HttpException(Response::HTTP_UNAUTHORIZED, 'You should be logged in to access this endpoint.');
    }
}
