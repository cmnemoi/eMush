<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Repository\PlayerRepositoryInterface;
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
        private PlayerRepositoryInterface $playerRepository
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
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
        /** @var Player $player */
        $player = $event->getStatusHolder();

        // only mark player as human if player has exchanged body
        // otherwise this means that player is dead and we don't want to mark him as human
        if ($event->hasTag(ActionEnum::EXCHANGE_BODY->value)) {
            $this->markPlayerAsHuman($player);
        }

        $this->removePlayerSpores($player);
        $this->playerRepository->save($player);
    }

    private function removePlayerSpores(Player $player): void
    {
        $sporeVariable = $player->getVariableByName(PlayerVariableEnum::SPORE);
        $sporeVariable->setValue(0)->setMaxValue(3);
    }

    private function markPlayerAsHuman(Player $player): void
    {
        $player->getPlayerInfo()->getClosedPlayer()->setIsMush(false);
    }
}
