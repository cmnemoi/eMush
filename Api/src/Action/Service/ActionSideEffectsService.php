<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class ActionSideEffectsService implements ActionSideEffectsServiceInterface
{
    public const ACTION_INJURY_MODIFIER = -2;

    private EventServiceInterface $eventService;
    private EventModifierServiceInterface $modifierService;

    public function __construct(
        EventServiceInterface $eventService,
        EventModifierServiceInterface $modifierService
    ) {
        $this->eventService = $eventService;
        $this->modifierService = $modifierService;
    }

    public function handleActionSideEffect(Action $action, Player $player, \DateTime $date): Player
    {
        $this->handleDirty($action, $player, $date);
        $this->handleInjury($action, $player, $date);

        return $player;
    }

    private function handleDirty(Action $action, Player $player, \DateTime $date): void
    {
        $baseDirtyRate = $action->getDirtyRate();
        $isSuperDirty = $baseDirtyRate > 100;

        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return;
        }

        if (!$isSuperDirty) {
            $isSoiled = $this->modifierService->isSuccessfulWithModifiers(
                $baseDirtyRate,
                [ModifierScopeEnum::EVENT_DIRTY],
                $action->getActionTags(),
                $date,
                $player,
            );

            if (!$isSoiled) {
                return;
            }
        }

        $statusEvent = new StatusEvent(PlayerStatusEnum::DIRTY, $player, $action->getActionTags(), new \DateTime());
        $statusEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    private function handleInjury(Action $action, Player $player, \DateTime $date): void
    {
        $baseInjuryRate = $action->getInjuryRate();

        $isHurt = $this->modifierService->isSuccessfulWithModifiers(
            $baseInjuryRate,
            [ModifierScopeEnum::EVENT_CLUMSINESS],
            $action->getActionTags(),
            $date,
            $player,
        );

        if ($isHurt) {
            $this->dispatchPlayerInjuryEvent($player, $date);
        }
    }

    private function dispatchPlayerInjuryEvent(Player $player, \DateTime $dateTime): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            self::ACTION_INJURY_MODIFIER,
            [ModifierScopeEnum::EVENT_CLUMSINESS],
            $dateTime
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
