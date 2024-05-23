<?php

declare(strict_types=1);

namespace Mush\Action\Listener;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Action\Service\ActionStrategyServiceInterface;
use Mush\Action\Service\PatrolShipManoeuvreServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    private ActionSideEffectsServiceInterface $actionSideEffectsService;
    private ActionStrategyServiceInterface $actionStrategyService;
    private PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService;
    private StatusServiceInterface $statusService;

    public function __construct(
        ActionSideEffectsServiceInterface $actionSideEffectsService,
        ActionStrategyServiceInterface $actionStrategyService,
        StatusServiceInterface $statusService,
        PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService
    ) {
        $this->actionSideEffectsService = $actionSideEffectsService;
        $this->actionStrategyService = $actionStrategyService;
        $this->statusService = $statusService;
        $this->patrolShipManoeuvreService = $patrolShipManoeuvreService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => ['onPreAction', 1],
            ActionEvent::POST_ACTION => 'onPostAction',
            ActionEvent::EXECUTE_ACTION => 'onExecuteAction',
        ];
    }

    public function onExecuteAction(ActionEvent $event): void
    {
        $actionConfig = $event->getActionConfig();
        $player = $event->getAuthor();
        $actionName = $actionConfig->getActionName();

        /** @var AbstractAction $action */
        $action = $this->actionStrategyService->getAction($actionName);

        if ($action === null) {
            throw new \Exception("this action is not implemented ({$actionName->value})");
        }

        $action->loadParameters(
            $actionConfig,
            $event->getActionProvider(),
            $player,
            $event->getActionTarget(),
            $event->getActionParameters()
        );
        $action->execute();
    }

    public function onPreAction(ActionEvent $event): void
    {
        $action = $event->getActionConfig();
        $player = $event->getAuthor();
        $status = $player->getStatusByName(PlayerStatusEnum::LYING_DOWN);

        if ($action->getActionName() !== ActionEnum::GET_UP
            && $status
        ) {
            /** @var Action $getUpAction */
            $getUpAction = $player
                ->getActions($player, ActionHolderEnum::PLAYER)
                ->filter(static fn (Action $action) => $action->getActionConfig()->getActionName() === ActionEnum::GET_UP)
                ->first();

            /** @var AbstractAction $getUpActionHandler */
            $getUpActionHandler = $this->actionStrategyService->getAction(ActionEnum::GET_UP);

            $getUpActionHandler->loadParameters(
                $getUpAction->getActionConfig(),
                $getUpAction->getActionProvider(),
                $player
            );
            $getUpActionHandler->execute();
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $actionConfig = $event->getActionConfig();
        $player = $event->getAuthor();
        $actionTarget = $event->getActionTarget();

        $this->actionSideEffectsService->handleActionSideEffect(
            $actionConfig,
            $event->getActionProvider(),
            $player,
            $actionTarget
        );

        $charge = $event->getActionProvider()->getUsedCharge($actionConfig->getActionName());
        if ($charge !== null) {
            $this->statusService->updateCharge($charge, -1, $event->getTags(), $event->getTime());
        }

        $player->getDaedalus()->addDailyActionPointsSpent($actionConfig->getActionCost());
    }
}
