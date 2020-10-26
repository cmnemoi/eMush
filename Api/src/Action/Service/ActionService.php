<?php

namespace Mush\Action\Service;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Service\DoorServiceInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ActionService implements ActionServiceInterface
{
    private array $actionsConfig;
    private ContainerInterface $container;
    private PlayerServiceInterface $playerService;
    private GameItemServiceInterface $itemService;
    private DoorServiceInterface $doorService;

    /**
     * ActionService constructor.
     * @param array $actionsConfig
     * @param ContainerInterface $container
     * @param PlayerServiceInterface $playerService
     * @param GameItemServiceInterface $itemService
     * @param DoorServiceInterface $doorService
     */
    public function __construct(
        array $actionsConfig,
        ContainerInterface $container,
        PlayerServiceInterface $playerService,
        GameItemServiceInterface $itemService,
        DoorServiceInterface $doorService
    ) {
        $this->actionsConfig = $actionsConfig;
        $this->container = $container;
        $this->playerService = $playerService;
        $this->itemService = $itemService;
        $this->doorService = $doorService;
    }

    private function getActionClass(Player $player, string $actionName, array $params): Action
    {
        $actionParams = $this->loadParameter($params);

        /** @var Action $action */
        $action = $this->container->get($this->actionsConfig[$actionName]);
        $action->loadParameters($player, $actionParams);

        return $action;
    }

    public function executeAction(Player $player, string $actionName, array $params): ActionResult
    {
        $action = $this->getActionClass($player, $actionName, $params);

        return $action->execute();
    }

    private function loadParameter($parameter): ActionParameters
    {
        $actionParams = new ActionParameters();

        if ($doorId = $parameter['door'] ?? null) {
            $door = $this->doorService->findById($doorId);
            $actionParams->setDoor($door);
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
