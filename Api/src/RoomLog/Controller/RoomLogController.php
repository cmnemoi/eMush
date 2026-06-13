<?php

declare(strict_types=1);

namespace Mush\RoomLog\Controller;

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
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/room-log')]
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
     */
    #[Route('', methods: ['GET'])]
    public function getRoomLogs(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME);

        $player = $this->getUserPlayer();

        $logs = $this->roomLogService->getRoomLog($player);

        return $this->json($logs, Response::HTTP_OK, [], ['currentPlayer' => $player]);
    }

    /**
     * Perform an action.
     */
    #[Route('/channel', methods: ['GET'])]
    public function getRoomLogChannel(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME);

        $player = $this->getUserPlayer();

        $language = $player->getLanguage();

        return $this->json([
            'name' => $this->translationService->translate('room_log.name', [], 'chat', $language),
            'description' => $this->translationService->translate('room_log.description', [], 'chat', $language),
            'scope' => ChannelScopeEnum::ROOM_LOG,
            'numberOfNewMessages' => $this->roomLogService->getNumberOfUnreadRoomLogsForPlayer($player),
            'flashing' => false,
        ]);
    }

    /**
     * Mark a room log as read.
     */
    #[Route('/read/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function readRoomLog(RoomLog $roomLog): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME);

        $player = $this->getUserPlayer();

        if ($player->getDaedalus() !== $roomLog->getDaedalusInfo()->getDaedalus()) {
            return $this->json(['error' => 'You are not from this Daedalus!'], Response::HTTP_FORBIDDEN);
        }

        $this->roomLogService->markRoomLogAsReadForPlayer($roomLog, $player);

        return $this->json(['detail' => 'Room log marked as read successfully'], Response::HTTP_OK);
    }

    /**
     * Mark all room logs as read.
     */
    #[Route('/all/read', methods: ['PATCH'])]
    public function markAllRoomLogsAsReadAction(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME);

        $this->roomLogService->markAllRoomLogsAsReadForPlayer($this->getUserPlayer());

        return $this->json(['detail' => 'All room logs marked as read successfully'], Response::HTTP_OK);
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
