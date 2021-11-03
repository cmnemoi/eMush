<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Service\GearModifierServiceInterface;
use Mush\Modifier\Service\ModifierService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private GearModifierServiceInterface $gearModifierService;
    private ModifierService $modifierService;

    public function __construct(
        GearModifierServiceInterface $gearModifierService,
        ModifierService $modifierService
    ) {
        $this->gearModifierService = $gearModifierService;
        $this->modifierService = $modifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => 'onPreAction',
            ActionEvent::RESULT_ACTION => 'onResultAction',
        ];
    }

    public function onResultAction(ActionEvent $event): void
    {
        $actionResult = $event->getActionResult();
        $player = $event->getPlayer();

        if ($actionResult === null) {
            return;
        }
        $actionName = $event->getAction()->getName();

        $target = $event->getActionParameter();

        // handle modifiers with charges
        $this->modifierService->consumeActionCharges($event->getAction(), $player, $target);

        switch ($actionName) {
        // handle gear modifiers when taken or dropped
            case ActionEnum::TAKE:
                if (!$target instanceof GameEquipment) {
                    throw new \LogicException('a game equipment should be given');
                }

                $this->gearModifierService->takeGear($target, $player);

                return;
            case ActionEnum::DROP:
                if (!$target instanceof GameEquipment) {
                    throw new \LogicException('a game equipment should be given');
                }

                $this->gearModifierService->dropGear($target, $player);

                return;

            // handle movement of a player
            case ActionEnum::MOVE:
                $this->modifierService->playerEnterRoom($player);
        }
    }

    public function onPreAction(ActionEvent $event): void
    {
        $player = $event->getPlayer();
        $actionName = $event->getAction()->getName();

        switch ($actionName) {
            case ActionEnum::MOVE:
                // handle movement of a player
                $this->modifierService->playerLeaveRoom($player);

                return;
        }
    }
}
