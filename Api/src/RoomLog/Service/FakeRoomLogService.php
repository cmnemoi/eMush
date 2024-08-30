<?php

declare(strict_types=1);

namespace Mush\RoomLog\Service;

use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Skill\Enum\SkillEnum;

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

    private function getVisibility(?Player $player, string $visibility): string
    {
        if ($player === null) {
            return $visibility;
        }

        $place = $player->getPlace();

        $placeHasAFunctionalCamera = $place->hasOperationalEquipmentByName(EquipmentEnum::CAMERA_EQUIPMENT);
        $placeHasAWitness = $place->getNumberOfPlayersAlive() > 1;
        $observantRevealsLog = $player->getAlivePlayersInRoomExceptSelf()->getPlayersWithSkill(SkillEnum::OBSERVANT)->count() > 0 && $this->d100Roll->isSuccessful(self::OBSERVANT_REVEAL_CHANCE);

        if ($visibility === VisibilityEnum::SECRET && ($placeHasAWitness || $placeHasAFunctionalCamera)) {
            return VisibilityEnum::REVEALED;
        }

        if ($visibility === VisibilityEnum::COVERT && ($placeHasAFunctionalCamera || $observantRevealsLog)) {
            return VisibilityEnum::REVEALED;
        }

        return $visibility;
    }

    private function setId(RoomLog $roomLog): RoomLog
    {
        (new \ReflectionProperty($roomLog, 'id'))->setValue($roomLog, crc32(uniqid()));

        return $roomLog;
    }
}
