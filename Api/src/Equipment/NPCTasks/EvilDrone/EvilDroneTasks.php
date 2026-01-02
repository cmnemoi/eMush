<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\EvilDrone;

use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationService;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class EvilDroneTasks
{
    public function __construct(
        private StatusServiceInterface $statusService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RandomServiceInterface $randomService,
        private RoomLogServiceInterface $roomLogService,
        private TranslationService $translationService,
    ) {}

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function executeTask(Drone $npc, StatusHolderInterface $target, \DateTime $time = new \DateTime())
    {
        match (true) {
            $this->targetIsDeadPlayer($target) => $this->taskRecycleBody($npc, $target, $time),
            $this->targetIsAlivePlayer($target) => $this->taskFlirt($npc, $target, $time),
            $this->targetIsNeron($target) => $this->taskConspire($npc, $target, $time),
            default => null
        };

        $this->statusService->removeStatus(
            EquipmentStatusEnum::EVIL_DRONE_TARGET,
            $npc,
            [],
            $time,
        );
    }

    public function executeIdleTask(Drone $npc, \DateTime $time = new \DateTime())
    {
        $placeEquipments = $npc->getPlace()->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() !== ItemEnum::EVIL_DRONE);

        if ($placeEquipments->isEmpty()) {
            return;
        }

        $randomEquipment = $this->randomService->getRandomElement($placeEquipments->toArray());

        $this->roomLogService->createLog(
            logKey: 'evil_drone.clean',
            place: $npc->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            dateTime: $time,
            parameters: ['drone' => $this->getLogName($npc), 'target_' . $randomEquipment->getLogKey() => $randomEquipment->getLogName()]
        );
    }

    private function targetIsDeadPlayer(StatusHolderInterface $target): bool
    {
        if ($target instanceof Player) {
            return $target->isDead();
        }

        return false;
    }

    private function targetIsAlivePlayer(StatusHolderInterface $target): bool
    {
        if ($target instanceof Player) {
            return $target->isAlive();
        }

        return false;
    }

    private function targetIsNeron(StatusHolderInterface $target): bool
    {
        return $target->getName() === EquipmentEnum::NERON_CORE;
    }

    private function taskRecycleBody(Drone $npc, Player $player, \DateTime $time)
    {
        $this->roomLogService->createLog(
            logKey: 'evil_drone.recycle',
            place: $npc->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            dateTime: $time,
            parameters: ['drone' => $this->getLogName($npc), 'target_character' => $player->getLogName()]
        );

        $ration = $this->gameEquipmentService->createGameEquipmentsFromName(
            GameRationEnum::COOKED_RATION,
            $npc->getPlace(),
            2,
            [],
            $time,
            VisibilityEnum::PUBLIC
        )[0];

        $this->createObjectFellLog($ration, $npc, $time);
        $this->createObjectFellLog($ration, $npc, $time);

        $npc->addDataToMemory($player->getLogName(), 'recycled');
    }

    private function createObjectFellLog(GameEquipment $item, Drone $npc, \DateTime $time)
    {
        $this->roomLogService->createLog(
            logKey: 'object_fell',
            place: $npc->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            dateTime: $time,
            parameters: ['character' => $this->getLogName($npc), 'target_' . $item->getLogKey() => $item->getLogName()]
        );
    }

    private function taskFlirt(Drone $npc, Player $player, \DateTime $time)
    {
        $this->roomLogService->createLog(
            logKey: 'evil_drone.flirt',
            place: $npc->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            dateTime: $time,
            parameters: ['drone' => $this->getLogName($npc), 'target_character' => $player->getLogName()]
        );

        $npc->addDataToMemory($player->getLogName(), 'flirted');
    }

    private function taskConspire(Drone $npc, GameEquipment $neron, \DateTime $time)
    {
        $randomPlayer = $this->randomService->getAlivePlayerInDaedalus($neron->getDaedalus());

        $this->roomLogService->createLog(
            logKey: 'evil_drone.conspire',
            place: $npc->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            dateTime: $time,
            parameters: ['drone' => $this->getLogName($npc), 'target_character' => $randomPlayer->getLogName()]
        );

        $npc->addDataToMemory($neron->getName(), $this->getCurrentCycle($npc));
    }

    private function getCurrentCycle(Drone $npc): int
    {
        return $npc->getDaedalus()->getDay() * $npc->getDaedalus()->getNumberOfCyclesPerDay() + $npc->getDaedalus()->getCycle();
    }

    private function getLogName(Drone $npc): string
    {
        return $this->translationService->translate(
            key: 'drone',
            parameters: ['drone_nickname' => $npc->getNickname(), 'drone_serial_number' => $npc->getSerialNumber()],
            domain: 'event_log',
            language: $npc->getDaedalus()->getLanguage()
        );
    }
}
