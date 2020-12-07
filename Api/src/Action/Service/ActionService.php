<?php

namespace Mush\Action\Service;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;

class ActionService implements ActionServiceInterface
{
    private array $actions = [];
    private PlayerServiceInterface $playerService;
    private GameEquipmentServiceInterface $equipmentService;

    /**
     * ActionService constructor.
     */
    public function __construct(
        PlayerServiceInterface $playerService,
        GameEquipmentServiceInterface $equipmentService
    ) {
        $this->playerService = $playerService;
        $this->equipmentService = $equipmentService;
    }

    public function addAction(Action $action)
    {
        $this->actions[$action->getActionName()] = $action;
    }

    public function getAction(string $actionName): ?Action
    {
        if (!isset($this->actions[$actionName])) {
            return null;
        }

        return $this->actions[$actionName];
    }

    public function executeAction(Player $player, string $actionName, array $params): ActionResult
    {
        $action = $this->getAction($actionName);

        if (null === $action) {
            return new Error('Action do not exist');
        }

        $actionParams = $this->loadParameter($params);
        $action->loadParameters($player, $actionParams);

        return $action->execute();
    }

    public function canExecuteAction(Player $player, string $actionName, ActionParameters $params): bool
    {
        $action = $this->getAction($actionName);

        if (null === $action) {
            return false;
        }

        $action->loadParameters($player, $params);

        return $action->canExecute();
    }

    private function loadParameter($parameter): ActionParameters
    {
        $actionParams = new ActionParameters();

        if ($doorId = $parameter['door'] ?? null) {
            $door = $this->equipmentService->findById($doorId);
            if ($door instanceof Door) {
                $actionParams->setDoor($door);
            }
        }
        if ($itemId = $parameter['item'] ?? null) {
            $item = $this->equipmentService->findById($itemId);
            if ($item instanceof GameItem) {
                $actionParams->setItem($item);
            }
        }
        if ($equipmentId = $parameter['equipment'] ?? null) {
            $equipment = $this->equipmentService->findById($equipmentId);
            $actionParams->setEquipment($equipment);
        }
        if ($playerId = $parameter['player'] ?? null) {
            $player = $this->playerService->findById($playerId);
            $actionParams->setPlayer($player);
        }

        if (($message = $parameter['message'] ?? null)) {
            $actionParams->setMessage($message);
        }

        return $actionParams;
    }
}
