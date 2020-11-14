<?php

namespace Mush\RoomLog\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Action\ActionResult\Error;
use Mush\Action\Service\ActionServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @param RoomLogServiceInterface $roomLogService
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
    public function createAction(): View
    {

        $logs = $this->roomLogService->getRoomLog($this->getUser()->getCurrentGame());

        return $this->view($logs);
    }
}
