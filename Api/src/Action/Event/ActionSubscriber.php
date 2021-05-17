<?php

namespace Mush\Action\Event;

use Mush\Action\Actions\GetUp;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogService;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private ActionSideEffectsServiceInterface $actionSideEffectsService;
    private GetUp $getUpAction;
    private GearToolServiceInterface $gearToolService;
    private RoomLogService $roomLogService;

    public function __construct(
        ActionSideEffectsServiceInterface $actionSideEffectsService,
        GetUp $getUp,
        GearToolServiceInterface $gearToolService,
        RoomLogService $roomLogService
    ) {
        $this->actionSideEffectsService = $actionSideEffectsService;
        $this->getUpAction = $getUp;
        $this->gearToolService = $gearToolService;
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => 'onPreAction',
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getPlayer();

        if ($action->getName() !== $this->getUpAction->getActionName() &&
            $lyingDownStatus = $player->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $getUpAction = $player->getCharacterConfig()->getActionByName(ActionEnum::GET_UP);

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
        $player = $event->getPlayer();

        $this->actionSideEffectsService->handleActionSideEffect($action, $player, new \DateTime());

        $this->gearToolService->applyChargeCost($player, $action->getName(), $action->getTypes());

        if (($actionResult = $event->getActionResult()) &&
            ($targetPlayer = $actionResult->getTargetPlayer()) &&
            in_array($action->getName(), ActionEnum::getForceGetUpActions()) &&
            $lyingDownStatus = $targetPlayer->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $targetPlayer->removeStatus($lyingDownStatus);

            $this->roomLogService->createLog(
                LogEnum::FORCE_GET_UP,
                $targetPlayer->getPlace(),
                VisibilityEnum::PUBLIC,
                'event_log',
                $targetPlayer,
                null,
                null,
                new \DateTime()
            );
        }
    }
}
