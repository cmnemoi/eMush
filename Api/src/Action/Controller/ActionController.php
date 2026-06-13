<?php

declare(strict_types=1);

namespace Mush\Action\Controller;

use Mush\Action\Entity\ActionResult\Error;
use Mush\Action\Entity\Dto\ActionRequest;
use Mush\Action\Service\ActionStrategyServiceInterface;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Service\CycleServiceInterface;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ActionController.
 */
class ActionController extends AbstractGameController
{
    private ActionStrategyServiceInterface $actionStrategyService;
    private CycleServiceInterface $cycleService;

    public function __construct(
        AdminServiceInterface $adminService,
        CycleServiceInterface $cycleService,
        ActionStrategyServiceInterface $actionStrategyService,
    ) {
        parent::__construct($adminService);
        $this->actionStrategyService = $actionStrategyService;
        $this->cycleService = $cycleService;
    }

    /**
     * Perform an action.
     */
    #[Route('/player/{id}/action', methods: ['POST'])]
    public function executeActionAction(Player $player, #[MapRequestPayload] ActionRequest $actionRequest): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();

        // @TODO: use Voter
        if ($player->getPlayerInfo()->getUser() !== $user) {
            throw new AccessDeniedException('player must be the same as user');
        }
        $daedalus = $player->getDaedalus();
        if ($daedalus->isDaedalusOrExplorationChangingCycle()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        try {
            $result = $this->actionStrategyService->executeAction(
                $player,
                $actionRequest->getAction(),
                $actionRequest->getParams() ?? []
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], 422);
        }

        if ($result instanceof Error) {
            return $this->json(['error' => $result->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY, [], ['currentPlayer' => $player]);
        }

        return $this->json([
            'actionResult' => $result->getName(),
            'actionDetails' => $result->getDetails(),
        ], Response::HTTP_OK, [], ['currentPlayer' => $player]);
    }
}
