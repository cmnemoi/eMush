<?php

namespace Mush\Action\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActionStrategyService implements ActionStrategyServiceInterface
{
    private array $actions = [];
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function addAction(AbstractAction $action): void
    {
        $this->actions[$action->getActionName()] = $action;
    }

    public function getAction(ActionEnum $actionName): ?AbstractAction
    {
        if (!isset($this->actions[$actionName->value])) {
            return null;
        }

        return $this->actions[$actionName->value];
    }

    public function executeAction(Player $player, int $actionId, array $params): ActionResult
    {
        $this->entityManager->beginTransaction();

        try {
            $result = $this->executeInTransaction($player, $actionId, $params);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            throw $e;
        }

        return $result;
    }

    private function executeInTransaction(Player $player, int $actionId, array $params): ActionResult
    {
        /** @var ActionConfig $actionConfig */
        $actionConfig = $this->entityManager->getRepository(ActionConfig::class)->find($actionId);
        if (!$actionConfig) {
            throw new NotFoundHttpException('This actionConfig does not exist');
        }
        $actionName = $actionConfig->getActionName();
        $actionService = $this->getAction($actionName);
        if (null === $actionService) {
            throw new \Exception("this action is not implemented ({$actionName->value})");
        }

        /** @var ?LogParameterInterface $target */
        $target = $this->loadGameEntity($params['target']);

        /** @var ActionProviderInterface $actionProvider */
        $actionProvider = $this->loadGameEntity($params['actionProvider']);
        $actionService->loadParameters($actionConfig, $actionProvider, $player, $target, $params);

        return $actionService->execute();
    }

    private function loadGameEntity(?array $entityParameters): null|ActionProviderInterface|LogParameterInterface
    {
        if ($entityParameters === null) {
            return null;
        }

        $className = $entityParameters['className'];
        $entityId = $entityParameters['id'];
        if ($entityId === null) {
            return null;
        }

        $gameEntity = $this->entityManager->getRepository($className)->find($entityId);

        if ($gameEntity instanceof ActionProviderInterface || $gameEntity instanceof LogParameterInterface) {
            return $gameEntity;
        }

        return null;
    }
}
