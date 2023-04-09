<?php

namespace Mush\Action\Listener;

use Mush\Action\Actions\GetUp;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private ActionSideEffectsServiceInterface $actionSideEffectsService;
    private GetUp $getUpAction;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        ActionSideEffectsServiceInterface $actionSideEffectsService,
        GetUp $getUp,
        GearToolServiceInterface $gearToolService,
    ) {
        $this->actionSideEffectsService = $actionSideEffectsService;
        $this->getUpAction = $getUp;
        $this->gearToolService = $gearToolService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => ['onPreAction', 1],
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getAuthor();

        if ($action->getActionName() !== $this->getUpAction->getActionName() &&
            $lyingDownStatus = $player->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $getUpAction = $player->getPlayerInfo()->getCharacterConfig()->getActionByName(ActionEnum::GET_UP);

            if ($getUpAction === null) {
                throw new \LogicException('character do not have get up action');
            }

            $this->getUpAction->loadParameters($getUpAction, $player);
            $this->getUpAction->execute();
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getAuthor();
        $actionParameter = $event->getActionParameter();

        $this->actionSideEffectsService->handleActionSideEffect($action, $player, new \DateTime());

        $this->gearToolService->applyChargeCost($player, $action->getActionName(), $action->getTypes());

        if ($actionParameter instanceof Player &&
            in_array($action->getActionName(), ActionEnum::getForceGetUpActions()) &&
            $lyingDownStatus = $actionParameter->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $actionParameter->removeStatus($lyingDownStatus);
        }

        $daedalus = $player->getDaedalus();
        $daedalus->addDailyActionPointsSpent($action->getActionCost());
    }
}
