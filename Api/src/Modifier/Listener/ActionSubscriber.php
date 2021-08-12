<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
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


        # handle gear modifiers
        $gameEquipment = $actionResult->getTargetEquipment();
        if ($actionName === ActionEnum::TAKE) {
            if ($gameEquipment === null) {
                throw new \LogicException('a game equipment should be given');
            }

            $this->gearModifierService->takeGear($gameEquipment, $player);
        } elseif ($actionName === ActionEnum::DROP) {
            if ($gameEquipment === null) {
                throw new \LogicException('a game equipment should be given');
            }

            $this->gearModifierService->dropGear($gameEquipment, $player);
        }
    }
}
