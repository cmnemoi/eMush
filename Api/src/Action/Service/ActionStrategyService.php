<?php

namespace Mush\Action\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;

class ActionStrategyService implements ActionStrategyServiceInterface
{
    private array $actions = [];
    private PlayerServiceInterface $playerService;
    private GameEquipmentServiceInterface $equipmentService;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
        PlayerServiceInterface $playerService,
        GameEquipmentServiceInterface $equipmentService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->playerService = $playerService;
        $this->equipmentService = $equipmentService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function addAction(AbstractAction $action): void
    {
        $this->actions[$action->getActionName()] = $action;
    }

    public function getAction(string $actionName): ?AbstractAction
    {
        if (!isset($this->actions[$actionName])) {
            $this->logger->warning("ActionStrategyService::getAction() - Action {$actionName} not found");
            return null;
        }

        return $this->actions[$actionName];
    }

    public function executeAction(Player $player, int $actionId, ?array $params): ActionResult
    {
        /** @var Action $action */
        $action = $this->entityManager->getRepository(Action::class)->find($actionId);

        if (!$action) {
            $errorMessage = "ActionStrategyService::executeAction() - Action not found";
            $this->logger->error($errorMessage, ['actionId' => $actionId]);
            throw new NotFoundHttpException($errorMessage);
        }

        $actionService = $this->getAction($action->getActionName());

        if (null === $actionService) {
            $errorMessage = "ActionStrategyService::executeAction() - Action do not exist";
            $this->logger->error($errorMessage, ['actionId' => $actionId]);
            return new Error($errorMessage);
        }

        $actionService->loadParameters($action, $player, $this->loadParameter($params));

        return $actionService->execute();
    }

    private function loadParameter(?array $parameter): ?LogParameterInterface
    {
        if ($parameter !== null) {
            if (($equipmentId = $parameter['door'] ?? null) ||
                ($equipmentId = $parameter['item'] ?? null) ||
                ($equipmentId = $parameter['equipment'] ?? null)
            ) {
                return $this->equipmentService->findById($equipmentId);
            }

            if ($playerId = $parameter['player'] ?? null) {
                return $this->playerService->findById($playerId);
            }
        }

        return null;
    }
}
