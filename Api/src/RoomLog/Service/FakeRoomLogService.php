<?php

declare(strict_types=1);

namespace Mush\RoomLog\Service;

use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\ValueObject\DaedalusDate;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

final class FakeRoomLogService implements RoomLogServiceInterface
{
    public const int OBSERVANT_REVEAL_CHANCE = 25;

    private array $roomLogs = [];

    public function __construct(private D100RollServiceInterface $d100Roll) {}

    public function createLog(
        string $logKey,
        Place $place,
        string $visibility,
        string $type,
        ?Player $player = null,
        array $parameters = [],
        ?\DateTime $dateTime = null
    ): RoomLog {
        $roomLog = new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setParameters($parameters)
            ->setType($type)
            ->setDaedalusInfo($place->getDaedalus()->getDaedalusInfo())
            ->setPlace($place->getName())
            ->setPlayerInfo($player?->getPlayerInfo())
            ->setBaseVisibility($visibility)
            ->setVisibility($this->getVisibility($player, $visibility))
            ->setCreatedAt($dateTime ?? new \DateTime('now'))
            ->setCycle($place->getDaedalus()->getCycle())
            ->setDay($place->getDaedalus()->getDay());

        return $this->persist($roomLog);
    }

    public function findById(int $id): ?RoomLog
    {
        return $this->roomLogs[$id] ?? null;
    }

    public function persist(RoomLog $roomLog): RoomLog
    {
        $roomLog = $this->setId($roomLog);
        $this->roomLogs[$roomLog->getId()] = $roomLog;

        return $roomLog;
    }

    public function markAllRoomLogsAsReadForPlayer(Player $player): void {}

    public function createLogFromActionEvent(ActionEvent $event): ?RoomLog
    {
        return null;
    }

    public function getRoomLog(Player $player): RoomLogCollection
    {
        return new RoomLogCollection($this->roomLogs);
    }

    public function findAllByDaedalusAndPlace(Daedalus $daedalus, Place $place): RoomLogCollection
    {
        return new RoomLogCollection();
    }

    public function getDaedalusRoomLogs(Daedalus $daedalus): RoomLogCollection
    {
        return new RoomLogCollection();
    }

    public function getNumberOfUnreadRoomLogsForPlayer(Player $player): int
    {
        return 0;
    }

    public function markRoomLogAsReadForPlayer(RoomLog $roomLog, Player $player): void {}

    public function clear(): void
    {
        $this->roomLogs = [];
    }

    public function findByPlayerAndLogKey(Player $player, string $logKey): ?RoomLog
    {
        foreach ($this->roomLogs as $roomLog) {
            if ($roomLog->getPlayerInfo() === $player->getPlayerInfo() && $roomLog->getLog() === $logKey) {
                return $roomLog;
            }
        }

        return null;
    }

    public function findAllByPlayerAndLogKey(Player $player, string $logKey): RoomLogCollection
    {
        $logs = [];
        foreach ($this->roomLogs as $roomLog) {
            if ($roomLog->getPlayerInfo() === $player->getPlayerInfo() && $roomLog->getLog() === $logKey) {
                $logs[] = $roomLog;
            }
        }

        return new RoomLogCollection($logs);
    }

    public function findAllByDaedalusPlaceAndCycle(Daedalus $daedalus, Place $place, int $cycle): RoomLogCollection
    {
        $logs = new RoomLogCollection();
        foreach ($this->roomLogs as $roomLog) {
            if ($roomLog->getDaedalus() === $daedalus && $roomLog->getPlace() === $place->getName() && $roomLog->getCycle() === $cycle) {
                $logs->add($roomLog);
            }
        }

        return $logs;
    }

    public function findByDaedalusAndLogKeyOrThrow(Daedalus $daedalus, string $logKey): RoomLog
    {
        foreach ($this->roomLogs as $roomLog) {
            if ($roomLog->getDaedalusInfo() === $daedalus->getDaedalusInfo() && $roomLog->getLog() === $logKey) {
                return $roomLog;
            }
        }

        throw new \RuntimeException("Log {$logKey} not found in daedalus {$daedalus->getId()}");
    }

    public function findOneByPlaceAndDaedalusDateOrThrow(string $logKey, Place $place, DaedalusDate $date): RoomLog
    {
        foreach ($this->roomLogs as $roomLog) {
            if (
                $roomLog->getLog() === $logKey
                && $roomLog->getDaedalusInfo() === $place->getDaedalus()->getDaedalusInfo()
                && $roomLog->getPlace() === $place->getName()
                && $roomLog->getDay() === $date->day
                && $roomLog->getCycle() === $date->cycle
            ) {
                return $roomLog;
            }
        }

        throw new \RuntimeException("Log was not found for given parameters: {$logKey} {$place->getName()} {$date->day} {$date->cycle}");
    }

    private function getVisibility(?Player $player, string $visibility): string
    {
        if ($player === null) {
            return $visibility;
        }

        $place = $player->getPlace();

        if ($place->hasStatus(PlaceStatusEnum::DELOGGED->toString())) {
            return VisibilityEnum::HIDDEN;
        }

        if ($this->observantRevealsLog($player, $visibility)) {
            $this->createObservantNoticeSomethingLog($player);

            return VisibilityEnum::REVEALED;
        }

        if ($visibility === VisibilityEnum::COVERT && $player->hasStatus(PlayerStatusEnum::PARIAH)) {
            $visibility = VisibilityEnum::SECRET;
        }

        if ($this->shouldRevealSecretLog($player, $visibility) || $this->shouldRevealCovertLog($player, $visibility)) {
            return VisibilityEnum::REVEALED;
        }

        return $visibility;
    }

    private function shouldRevealCovertLog(Player $player, string $visibility): bool
    {
        $place = $player->getPlace();
        $placeHasAFunctionalCamera = $place->hasOperationalEquipmentByName(EquipmentEnum::CAMERA_EQUIPMENT);

        return $visibility === VisibilityEnum::COVERT && $placeHasAFunctionalCamera;
    }

    private function shouldRevealSecretLog(Player $player, string $visibility): bool
    {
        $place = $player->getPlace();
        $placeHasAWitness = $place->getNumberOfPlayersAlive() > 1;
        $placeHasAFunctionalCamera = $place->hasOperationalEquipmentByName(EquipmentEnum::CAMERA_EQUIPMENT);

        return $visibility === VisibilityEnum::SECRET && ($placeHasAWitness || $placeHasAFunctionalCamera);
    }

    private function observantRevealsLog(Player $player, string $visibility): bool
    {
        $observantInRoom = $player->getAlivePlayersInRoomExceptSelf()->hasPlayerWithSkill(SkillEnum::OBSERVANT);
        $observantDetectedCovertAction = $visibility === VisibilityEnum::COVERT && $this->d100Roll->isSuccessful(self::OBSERVANT_REVEAL_CHANCE);

        return $observantInRoom && $observantDetectedCovertAction;
    }

    private function createObservantNoticeSomethingLog(Player $player): void
    {
        $observant = $player->getAlivePlayersInRoomExceptSelf()->getOnePlayerWithSkillOrThrow(SkillEnum::OBSERVANT);
        $this->createLog(
            LogEnum::OBSERVANT_NOTICED_SOMETHING,
            $observant->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $observant,
            [$observant->getLogKey() => $observant->getLogName()],
            new \DateTime(),
        );
    }

    private function setId(RoomLog $roomLog): RoomLog
    {
        (new \ReflectionProperty($roomLog, 'id'))->setValue($roomLog, crc32(uniqid()));

        return $roomLog;
    }
}
