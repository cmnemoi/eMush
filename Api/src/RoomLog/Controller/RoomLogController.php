<?php

namespace Mush\RoomLog\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Repository\PlayerInfoRepository;
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
class RoomLogController extends AbstractGameController
{
    private RoomLogServiceInterface $roomLogService;
    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;
    private PlayerInfoRepository $playerInfoRepository;

    public function __construct(
        AdminServiceInterface $adminService,
        RoomLogServiceInterface $roomLogService,
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService,
        PlayerInfoRepository $playerInfoRepository
    ) {
        parent::__construct($adminService);
        $this->roomLogService = $roomLogService;
        $this->cycleService = $cycleService;
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

        /** @var User $user */
        $user = $this->getUser();

        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);

        if ($playerInfo === null
            || !($player = $playerInfo->getPlayer())
        ) {
            throw new AccessDeniedException();
        }

        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        $logs = $this->roomLogService->getRoomLog($player);

        $context = new Context();
        $context
            ->setAttribute('currentPlayer', $player)
        ;

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

        /** @var User $user */
        $user = $this->getUser();

        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);

        if ($playerInfo === null
            || !($player = $playerInfo->getPlayer())
        ) {
            throw new AccessDeniedException();
        }

        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        $language = $daedalus->getLanguage();

        return $this->view([
            'name' => $this->translationService->translate('room_log.name', [], 'chat', $language),
            'description' => $this->translationService->translate('room_log.description', [], 'chat', $language),
            'scope' => ChannelScopeEnum::ROOM_LOG,
        ]);
    }
}
