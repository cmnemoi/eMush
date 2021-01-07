<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActionService implements ActionServiceInterface
{
    const ACTION_INJURY_MODIFIER = -2;

    private EventDispatcherInterface $eventDispatcher;
    private RandomServiceInterface $randomService;
    private StatusServiceInterface $statusService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
    }

    public function handleActionSideEffect(Action $action, Player $player, ?\DateTime $date = null): Player
    {
        $dirtyRate = $action->getDirtyRate();
        if (!$player->hasStatus(PlayerStatusEnum::DIRTY) &&
            $dirtyRate > 0 && $this->randomService->randomPercent() < $dirtyRate) {
            if ($player->hasItemByName(GearItemEnum::STAINPROOF_APRON)) {
                $this->roomLogService->createPlayerLog(
                    LogEnum::SOIL_PREVENTED,
                    $player->getRoom(),
                    $player,
                    VisibilityEnum::PRIVATE,
                    $date
                );
            } else {
                $dirtyStatus = $this->statusService->createCorePlayerStatus(PlayerStatusEnum::DIRTY, $player);
                $player->addStatus($dirtyStatus);
                $this->roomLogService->createPlayerLog(
                    LogEnum::SOILED,
                    $player->getRoom(),
                    $player,
                    VisibilityEnum::PRIVATE,
                    $date
                );
            }
        }

        $injuryRate = $action->getInjuryRate();
        if ($injuryRate > 0 && $this->randomService->randomPercent() < $injuryRate) {
            $this->roomLogService->createPlayerLog(
                LogEnum::CLUMSINESS,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                $date
            );
            $this->dispatchPlayerInjuryEvent($player, $date);
        }

        return $player;
    }

    private function dispatchPlayerInjuryEvent(Player $player, ?\DateTime $dateTime = null): void
    {
        $playerActionModifier = new ActionModifier();
        $playerActionModifier->setHealthPointModifier(self::ACTION_INJURY_MODIFIER);
        $playerEvent = new PlayerEvent($player, $dateTime);
        $playerEvent->setActionModifier($playerActionModifier);
        $playerEvent->setReason(EndCauseEnum::CLUMSINESS);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
    }
}
