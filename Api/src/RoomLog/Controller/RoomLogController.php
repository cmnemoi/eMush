<?php

namespace Mush\RoomLog\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Game\Service\CycleServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersController.
 *
 * @Route(path="/room-log")
 */
class RoomLogController extends AbstractFOSRestController
{
    private RoomLogServiceInterface $roomLogService;
    private CycleServiceInterface $cycleService;

    public function __construct(RoomLogServiceInterface $roomLogService, CycleServiceInterface $cycleService)
    {
        $this->roomLogService = $roomLogService;
        $this->cycleService = $cycleService;
    }

    /**
     * Perform an action.
     *
     * @OA\Tag(name="RoomLog")
     * @Security(name="Bearer")
     * @Rest\GET(path="")
     */
    public function getRoomLogs(): View
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME);

        /** @var User $user */
        $user = $this->getUser();

        if (!$player = $user->getCurrentGame()) {
            throw new AccessDeniedException();
        }

        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleCycleChange(new \DateTime(), $daedalus);

        $logs = $this->roomLogService->getRoomLog($player);

        return $this->view($logs);
    }
}
