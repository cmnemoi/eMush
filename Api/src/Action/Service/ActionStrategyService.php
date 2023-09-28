<?php

namespace Mush\Action\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Error;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Hunter\Service\HunterServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActionStrategyService implements ActionStrategyServiceInterface
{
    private array $actions = [];
    private PlayerServiceInterface $playerService;
    private GameEquipmentServiceInterface $equipmentService;
    private HunterServiceInterface $hunterService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PlayerServiceInterface $playerService,
        GameEquipmentServiceInterface $equipmentService,
        HunterServiceInterface $hunterService,
        EntityManagerInterface $entityManager
    ) {
        $this->playerService = $playerService;
        $this->equipmentService = $equipmentService;
        $this->hunterService = $hunterService;
        $this->entityManager = $entityManager;
    }

    public function addAction(AbstractAction $action): void
    {
        $this->actions[$action->getActionName()] = $action;
    }

    public function getAction(string $actionName): ?AbstractAction
    {
        if (!isset($this->actions[$actionName])) {
            return null;
        }

        return $this->actions[$actionName];
    }

    public function executeAction(Player $player, int $actionId, array $params): ActionResult
    {
        /** @var Action $action */
        $action = $this->entityManager->getRepository(Action::class)->find($actionId);

        if (!$action) {
            throw new NotFoundHttpException('This action does not exist');
        }

        $actionService = $this->getAction($action->getActionName());

        if (null === $actionService) {
            return new Error('Action do not exist');
        }

        $actionSupport = $this->loadActionSupport($params['actionSupport']);
        $actionService->loadParameters($action, $player, $actionSupport, $params);

        return $actionService->execute();
    }

    private function loadActionSupport(?array $actionSupport): ?LogParameterInterface
    {
        if ($actionSupport !== null) {
            if (($equipmentId = $actionSupport['door'] ?? null)
                || ($equipmentId = $actionSupport['item'] ?? null)
                || ($equipmentId = $actionSupport['equipment'] ?? null)
            ) {
                return $this->equipmentService->findById($equipmentId);
            }

            if ($playerId = $actionSupport['player'] ?? null) {
                return $this->playerService->findById($playerId);
            }

            if ($hunterId = $actionSupport['hunter'] ?? null) {
                return $this->hunterService->findById($hunterId);
            }
        }

        return null;
    }
}
