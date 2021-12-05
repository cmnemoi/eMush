<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActionSideEffectsService implements ActionSideEffectsServiceInterface
{
    public const ACTION_INJURY_MODIFIER = -2;

    private EventDispatcherInterface $eventDispatcher;
    private ModifierServiceInterface $modifierService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ModifierServiceInterface $modifierService
    ) {
        $this->eventDispatcher = $eventDispatcher;
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
                $action->getName(),
                $date,
                $player,
            );

            if (!$isSoiled) {
                return;
            }
        }

        $statusEvent = new StatusEvent(PlayerStatusEnum::DIRTY, $player, EventEnum::NEW_DAY, new \DateTime());
        $statusEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    private function handleInjury(Action $action, Player $player, \DateTime $date): void
    {
        $baseInjuryRate = $action->getInjuryRate();

        $isHurt = $this->modifierService->isSuccessfulWithModifiers(
            $baseInjuryRate,
            [ModifierScopeEnum::EVENT_CLUMSINESS],
            $action->getName(),
            $date,
            $player,
        );

        if ($isHurt) {
            $this->dispatchPlayerInjuryEvent($player, $date);
        }
    }

    private function dispatchPlayerInjuryEvent(Player $player, \DateTime $dateTime): void
    {
        $playerModifierEvent = new PlayerModifierEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            self::ACTION_INJURY_MODIFIER,
            EndCauseEnum::CLUMSINESS,
            $dateTime
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }
}
