<?php

namespace Mush\Action\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Entity;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActionService implements ActionServiceInterface
{
    private array $actions = [];
    private PlayerServiceInterface $playerService;
    private GameEquipmentServiceInterface $equipmentService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PlayerServiceInterface $playerService,
        GameEquipmentServiceInterface $equipmentService,
        EntityManagerInterface $entityManager
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

    public function executeAction(Player $player, int $actionId, array $params): ActionResult
    {
        /** @var Action $action */
        $action = $this->entityManager->getRepository(Action::class)->find($actionId);

        if (!$action) {
            throw new NotFoundHttpException('This action does not exist');
        }

        $actionService = $this->getAction($action->getName());

        if (null === $actionService) {
            return new Error('Action do not exist');
        }

        $actionParams = $this->loadParameter($params);
        $actionService->loadParameters($player, $actionParams);

        return $actionService->execute();
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

    private function loadParameter(array $parameter): ActionParameters
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
