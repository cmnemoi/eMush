<?php

namespace Mush\Action\Service;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Item\Entity\Door;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;

class ActionService implements ActionServiceInterface
{
    private array $actions = [];
    private PlayerServiceInterface $playerService;
    private GameItemServiceInterface $itemService;

    /**
     * ActionService constructor.
     */
    public function __construct(
        PlayerServiceInterface $playerService,
        GameItemServiceInterface $itemService
    ) {
        $this->playerService = $playerService;
        $this->itemService = $itemService;
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
            $door = $this->itemService->findById($doorId);
            if ($door instanceof Door) {
                $actionParams->setDoor($door);
            }
        }
        if ($itemId = $parameter['item'] ?? null) {
            $item = $this->itemService->findById($itemId);
            $actionParams->setItem($item);
        }
        if ($playerId = $parameter['player'] ?? null) {
            $player = $this->playerService->findById($playerId);
            $actionParams->setPlayer($player);
        }

        return $actionParams;
    }
}
