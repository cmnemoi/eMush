<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\RoomLog\Service\RoomLogService;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StatusEventSubscriber implements EventSubscriberInterface
{
    public const FOUND_MORALE_BOOST = 3;

    public function __construct(
        private DeletePlayerSkillService $deletePlayerSkill,
        private EventServiceInterface $eventService,
        private PlayerRepositoryInterface $playerRepository,
        private RoomLogService $roomLogService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $statusName = $event->getStatusName();

        switch ($statusName) {
            case PlayerStatusEnum::INACTIVE:
            case PlayerStatusEnum::HIGHLY_INACTIVE:
                $DroppableItems = $event->getPlayerStatusHolder()->getEquipmentsExceptPersonal();
                foreach ($DroppableItems as $item) {
                    $tags = $event->getTags();
                    $tags[] = $item->getName();

                    $itemEvent = new MoveEquipmentEvent(
                        equipment: $item,
                        newHolder: $event->getPlayerStatusHolder()->getPlace(),
                        author: $event->getPlayerStatusHolder(),
                        visibility: VisibilityEnum::PUBLIC,
                        tags: $tags,
                        time: new \DateTime(),
                    );
                    $this->eventService->callEvent($itemEvent, EquipmentEvent::CHANGE_HOLDER);
                }

                break;
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $statusName = $event->getStatusName();

        switch ($statusName) {
            case PlayerStatusEnum::LOST:
                $this->giveMoraleToReturnedPlayer($event);

                break;

            case PlayerStatusEnum::MUSH:
                $this->handleMushStatusRemoved($event);

                break;
        }
    }

    private function giveMoraleToReturnedPlayer(StatusEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getStatusHolder();
        $playerVariableEvent = new PlayerVariableEvent(
            player: $player,
            variableName: PlayerVariableEnum::MORAL_POINT,
            quantity: self::FOUND_MORALE_BOOST,
            tags: $event->getTags(),
            time: $event->getTime(),
        );

        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function handleMushStatusRemoved(StatusEvent $event): void
    {
        $player = $event->getPlayerStatusHolder();

        if ($player->isAlive()) {
            $this->markPlayerAsHuman($player);
        }
        $this->removePlayerSpores($player);
    }

    private function removePlayerSpores(Player $player): void
    {
        $sporeVariable = $player->getVariableByName(PlayerVariableEnum::SPORE);
        $sporeVariable->setValue(0)->setMaxValue(3);
        $this->playerRepository->save($player);
    }

    private function markPlayerAsHuman(Player $player): void
    {
        $player->getPlayerInfo()->getClosedPlayer()->setIsMush(false);
        $this->playerRepository->save($player);
    }
}
