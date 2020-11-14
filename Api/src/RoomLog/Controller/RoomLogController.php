<?php

namespace Mush\RoomLog\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\User\Voter\UserVoter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersController.
 *
 * @Route(path="/room-log")
 */
class RoomLogController extends AbstractFOSRestController
{
    private RoomLogServiceInterface $roomLogService;

    /**
     * RoomLogController constructor.
     */
    public function __construct(RoomLogServiceInterface $roomLogService)
    {
        $this->roomLogService = $roomLogService;
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

        $logs = $this->roomLogService->getRoomLog($this->getUser()->getCurrentGame());

        return $this->view($logs);
    }
}
