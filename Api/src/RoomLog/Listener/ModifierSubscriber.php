<?php

namespace Mush\RoomLog\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModifierSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;
    private RandomServiceInterface $randomService;

    public function __construct(RoomLogServiceInterface $roomLogService, RandomServiceInterface $randomService)
    {
        $this->roomLogService = $roomLogService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ModifierEvent::APPLY_MODIFIER => 'onApplyModifier',
        ];
    }

    public function onApplyModifier(ModifierEvent $event): void
    {
        $modifierName = $event->getModifier()->getModifierConfig()->getModifierName();
        $logMap = LogEnum::MODIFIER_LOG_ENUM[LogEnum::VALUE];

        if ($modifierName !== null && \array_key_exists($modifierName, $logMap)) {
            $logKey = $logMap[$modifierName];
            $logVisibility = LogEnum::MODIFIER_LOG_ENUM[LogEnum::VISIBILITY][$modifierName];

            $this->createEventLog($logKey, $logVisibility, $event);
        }
    }

    private function createEventLog(string $logKey, string $logVisibility, ModifierEvent $event): void
    {
        $modifier = $event->getModifier();
        $holder = $modifier->getModifierHolder();
        $player = $event->getAuthor();
        $logParameters = $event->getLogParameters();

        switch (true) {
            case $holder instanceof Player:
                $player = $holder;
                $place = $player->getPlace();

                break;

            case $holder instanceof Place:
                $place = $holder;

                break;

            case $holder instanceof GameEquipment:
                $place = $holder->getPlace();

                break;

            case $holder instanceof Daedalus:
            default:
                return;
        }

        // Log for disabled require to get another player in the room
        if ($logKey === LogEnum::HELP_DISABLED && $player instanceof Player) {
            $otherPlayers = $player->getPlace()->getPlayers()->getPlayerAlive()->filter(
                static fn (Player $otherPlayer) => ($player->getLogName() !== $otherPlayer->getLogName())
            );
            if ($otherPlayers->isEmpty()) {
                throw new \LogicException('there should be another player in the room for this modifier to trigger');
            }

            $helper = $this->randomService->getRandomPlayer($otherPlayers);
            $logParameters[$helper->getLogKey()] = $helper->getLogName();
        }

        $this->roomLogService->createLog(
            $logKey,
            $place,
            $logVisibility,
            'event_log',
            $player,
            $logParameters,
            $event->getTime()
        );
    }
}
