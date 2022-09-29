<?php

namespace Mush\Action\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActionStrategyService implements ActionStrategyServiceInterface
{
    private array $actions = [];
    private PlayerServiceInterface $playerService;
    private EquipmentServiceInterface $equipmentService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PlayerServiceInterface    $playerService,
        EquipmentServiceInterface $equipmentService,
        EntityManagerInterface    $entityManager
    ) {
        $this->playerService = $playerService;
        $this->equipmentService = $equipmentService;
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

    public function executeAction(Player $player, int $actionId, ?array $params): ActionResult
    {
        /** @var Action $action */
        $action = $this->entityManager->getRepository(Action::class)->find($actionId);

        if (!$action) {
            throw new NotFoundHttpException('This action does not exist');
        }

        $actionService = $this->getAction($action->getName());

        if (!$actionService) {
            return new Error('Action do not exist');
        }

        $actionService->loadParameters($action, $player, $this->loadParameter($params));

        return $actionService->execute();
    }

    private function loadParameter(?array $parameter): ?LogParameterInterface
    {
        if ($parameter === null) {
            return null;
        }

        if ($equipmentId = $this->getEquipmentId($parameter)) {
            return $this->equipmentService->findById($equipmentId);
        }

        if ($playerId = $this->getPlayerId($parameter)) {
            return $this->playerService->findById($playerId);
        }

        return null;
    }

    private function getEquipmentId(?array $parameter) : string | null {
        return $parameter['door'] ?? $parameter['item'] ?? $parameter['equipment'] ?? null;
    }

    private function getPlayerId(?array $parameter) : string | null {
        return $parameter['player'] ?? null;
    }
}
