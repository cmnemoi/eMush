<?php

namespace Mush\RoomLog\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RoomLogController.
 *
 * @Route(path="/room-log")
 */
class RoomLogController extends AbstractGameController
{
    private RoomLogServiceInterface $roomLogService;
    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;
    private PlayerInfoRepository $playerInfoRepository;

    public function __construct(
        AdminServiceInterface $adminService,
        RoomLogServiceInterface $roomLogService,
        CycleServiceInterface $cycleStrategyService,
        TranslationServiceInterface $translationService,
        PlayerInfoRepository $playerInfoRepository
    ) {
        parent::__construct($adminService);
        $this->roomLogService = $roomLogService;
        $this->cycleService = $cycleStrategyService;
        $this->translationService = $translationService;
        $this->playerInfoRepository = $playerInfoRepository;
    }

    /**
     * Perform an action.
     *
     * @OA\Tag(name="RoomLog")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="")
     */
    public function getRoomLogs(): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME);

        $player = $this->getUserPlayer();

        $logs = $this->roomLogService->getRoomLog($player);

        $context = new Context();
        $context
            ->setAttribute('currentPlayer', $player);

        $view = $this->view($logs);
        $view->setContext($context);

        return $view;
    }

    /**
     * Perform an action.
     *
     * @OA\Tag(name="RoomLog")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/channel")
     */
    public function getRoomLogChannel(): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME);

        $player = $this->getUserPlayer();

        $language = $player->getLanguage();

        return $this->view([
            'name' => $this->translationService->translate('room_log.name', [], 'chat', $language),
            'description' => $this->translationService->translate('room_log.description', [], 'chat', $language),
            'scope' => ChannelScopeEnum::ROOM_LOG,
            'numberOfNewMessages' => $this->roomLogService->getNumberOfUnreadRoomLogsForPlayer($player),
        ]);
    }

    /**
     * Mark a room log as read.
     *
     * @OA\Tag(name="RoomLog")
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The room log id",
     *      required=true
     * )
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch (path="/read/{id}", requirements={"id"="\d+"})
     */
    public function readRoomLog(RoomLog $roomLog): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME);

        $player = $this->getUserPlayer();

        if ($player->getDaedalus() !== $roomLog->getDaedalusInfo()->getDaedalus()) {
            return $this->view(['error' => 'You are not from this Daedalus!'], Response::HTTP_FORBIDDEN);
        }

        $this->roomLogService->markRoomLogAsReadForPlayer($roomLog, $player);

        return $this->view(['detail' => 'Room log marked as read successfully'], Response::HTTP_OK);
    }

    /**
     * Mark all room logs as read.
     *
     * @OA\Tag(name="RoomLog")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/all/read")
     */
    public function markAllRoomLogsAsReadAction(): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME);

        $this->roomLogService->markAllRoomLogsAsReadForPlayer($this->getUserPlayer());

        return $this->view(['detail' => 'All room logs marked as read successfully'], Response::HTTP_OK);
    }

    private function getUserPlayer(): Player
    {
        /** @var User $user */
        $user = $this->getUser();

        $player = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user)?->getPlayer();

        if (!$player) {
            throw new AccessDeniedException();
        }

        return $player;
    }
}
