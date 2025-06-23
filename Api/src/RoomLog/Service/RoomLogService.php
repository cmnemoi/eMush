<?php

declare(strict_types=1);

namespace Mush\RoomLog\Service;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Chat\Enum\NeronPersonalitiesEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\ValueObject\GameDate;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\D100RollServiceInterface as D100RollInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface as GetRandomIntegerInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogDeclinationEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Repository\RoomLogRepositoryInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;

final class RoomLogService implements RoomLogServiceInterface
{
    public const int OBSERVANT_REVEAL_CHANCE = 25;

    public function __construct(
        private D100RollInterface $d100Roll,
        private GetRandomIntegerInterface $getRandomInteger,
        private RoomLogRepositoryInterface $roomLogRepository,
        private TranslationServiceInterface $translationService,
    ) {}

    public function persist(RoomLog $roomLog): RoomLog
    {
        $this->roomLogRepository->save($roomLog);

        return $roomLog;
    }

    public function createLogFromActionEvent(ActionEvent $event): ?RoomLog
    {
        $actionResult = $event->getActionResult();
        $actionName = $event->getActionConfig()->getActionName();
        $actionParameter = $event->getActionTarget();
        $player = $event->getAuthor();
        $time = $event->getTime();

        // first lets handle the special case of examine action
        if ($actionName === ActionEnum::EXAMINE) {
            return $this->createExamineLog($player, $actionParameter);
        }

        $logMapping = ActionLogEnum::ACTION_LOGS[$actionName->value] ?? null;

        if (!$logMapping) {
            return null;
        }

        $actionResultString = $actionResult?->getName() ?? '';
        if (isset($logMapping[$actionResultString])) {
            $logData = $logMapping[$actionResultString];
        } else {
            return null;
        }

        $parameters = $this->getActionLogParameters($event);

        $visibility = $actionResult?->getVisibility() ?? VisibilityEnum::HIDDEN;
        if ($actionParameter instanceof GameEquipment && $actionParameter->getEquipment()->isPersonal()) {
            $visibility = VisibilityEnum::PRIVATE;
        }

        return $this->createLog(
            $logData,
            $player->getPlace(),
            $visibility,
            'actions_log',
            $player,
            $parameters,
            $time
        );
    }

    public function createLog(
        string $logKey,
        Place $place,
        string $visibility,
        string $type,
        ?Player $player = null,
        array $parameters = [],
        ?\DateTime $dateTime = null
    ): RoomLog {
        if (ActionLogEnum::dependsOnNeronMood($logKey)) {
            $parameters['neronMood'] = $this->getNeronPersonality($place->getDaedalus());
        }

        // if there is several version of the log
        if (\array_key_exists($logKey, $declinations = LogDeclinationEnum::getVersionNumber())) {
            foreach ($declinations[$logKey] as $keyVersion => $versionNb) {
                $parameters[$keyVersion] = $this->getRandomInteger->execute(1, $versionNb);
            }
        }

        $roomLog = new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setParameters($parameters)
            ->setType($type)
            ->setDaedalusInfo($place->getDaedalus()->getDaedalusInfo())
            ->setPlace($place->getName())
            ->setPlayerInfo($player?->getPlayerInfo())
            ->setBaseVisibility($visibility)
            ->setCreatedAt($dateTime ?? new \DateTime('now'))
            ->setCycle($place->getDaedalus()->getCycle())
            ->setDay($place->getDaedalus()->getDay());

        $visibility = $this->getVisibility($roomLog, $place);
        $roomLog->setVisibility($visibility);

        return $this->persist($roomLog);
    }

    public function getRoomLog(Player $player): RoomLogCollection
    {
        return new RoomLogCollection($this->roomLogRepository->getPlayerRoomLog($player));
    }

    public function getDaedalusRoomLogs(Daedalus $daedalus): RoomLogCollection
    {
        return new RoomLogCollection($this->roomLogRepository->getAllRoomLogsByDaedalus($daedalus));
    }

    public function findAllByDaedalusAndPlace(Daedalus $daedalus, Place $place): RoomLogCollection
    {
        return new RoomLogCollection($this->roomLogRepository->findAllByDaedalusAndPlace($daedalus, $place));
    }

    public function getNumberOfUnreadRoomLogsForPlayer(Player $player): int
    {
        return $this->getRoomLog($player)->filter(
            static fn (RoomLog $roomLog) => $roomLog->isUnreadBy($player)
        )->count();
    }

    public function markRoomLogAsReadForPlayer(RoomLog $roomLog, Player $player): void
    {
        try {
            $roomLog->addReader($player)->cancelTimestampable(); // We don't want to update the updatedAt field when player reads the log because this would change the order of the messages
            $this->roomLogRepository->save($roomLog);
        } catch (UniqueConstraintViolationException $e) {
            // ignore as this is probably due to a race condition
        }
    }

    public function markAllRoomLogsAsReadForPlayer(Player $player): void
    {
        $unreadLogs = $this->getRoomLog($player)->getUnreadForPlayer($player);

        try {
            $this->roomLogRepository->startTransaction();
            $unreadLogs->map(fn (RoomLog $roomLog) => $this->markRoomLogAsReadForPlayer($roomLog, $player));
            $this->roomLogRepository->commitTransaction();
        } catch (UniqueConstraintViolationException $e) {
            // ignore as this is probably due to a race condition
        } catch (\Throwable $e) {
            $this->roomLogRepository->rollbackTransaction();

            throw $e;
        }
    }

    public function findOneByPlaceAndDaedalusDateOrThrow(string $logKey, Place $place, GameDate $date): RoomLog
    {
        $parameters = [
            'log' => $logKey,
            'daedalusInfo' => $place->getDaedalus()->getDaedalusInfo()->getId(),
            'place' => $place->getName(),
            'day' => $date->day(),
            'cycle' => $date->cycle(),
        ];

        $roomLog = $this->roomLogRepository->getOneBy($parameters);
        if (!$roomLog) {
            throw new \RuntimeException("Log was not found for given parameters: {$this->parametersToString($parameters)}");
        }

        return $roomLog;
    }

    public function findAllByPlaceAndDaedalusDate(Place $place, GameDate $date): RoomLogCollection
    {
        $logs = $this->roomLogRepository->getBy([
            'daedalusInfo' => $place->getDaedalus()->getDaedalusInfo(),
            'place' => $place->getName(),
            'day' => $date->day(),
            'cycle' => $date->cycle(),
        ]);

        return new RoomLogCollection($logs);
    }

    private function getVisibility(RoomLog $roomLog, Place $place): string
    {
        $player = $roomLog->getPlayerInfo()?->getPlayer();
        $visibility = $roomLog->getBaseVisibility();

        if ($place->hasStatus(PlaceStatusEnum::DELOGGED->toString())) {
            return VisibilityEnum::HIDDEN;
        }

        if ($player === null) {
            return $visibility;
        }

        if ($roomLog->shouldBeSecretForPlayer()) {
            $visibility = VisibilityEnum::SECRET;
        }

        if ($this->observantRevealsLog($player, $visibility)) {
            $this->createObservantNoticeSomethingLog($player);
            $roomLog->markAsNoticed();

            return VisibilityEnum::REVEALED;
        }

        if ($this->shouldRevealLog($roomLog, $visibility)) {
            return VisibilityEnum::REVEALED;
        }

        return $visibility;
    }

    private function getActionLogParameters(ActionEvent $event): array
    {
        $actionName = $event->getActionConfig()->getActionName();
        $actionResult = $event->getActionResult();
        $actionParameter = $event->getActionTarget();
        $actionProvider = $event->getActionProvider();
        $player = $event->getAuthor();

        $parameters = [];
        $parameters[$player->getLogKey()] = $player->getLogName();

        if (($quantity = $actionResult?->getQuantity()) !== null) {
            $parameters['quantity'] = $quantity;
        }
        if ($actionParameter !== null) {
            $key = 'target_' . $actionParameter->getLogKey();
            $parameters[$key] = $actionParameter->getLogName();

            if (str_contains($key, 'planet')) {
                $planet = $event->getActionTargetAsPlanet();
                $parameters[$key] = $this->translatePlanetName($planet, $player);
            }
        }
        if (($equipment = $actionResult?->getEquipment()) !== null) {
            $parameters[$equipment->getLogKey()] = $equipment->getLogName();
        }
        if ($actionName === ActionEnum::GRAFT) {
            /** @var GameItem $fruit */
            $fruit = $actionProvider;
            $parameters['item'] = $fruit->getLogName();
        }

        return $parameters;
    }

    private function translatePlanetName(Planet $planet, Player $player): string
    {
        return $this->translationService->translate(
            key: 'planet_name',
            parameters: $planet->getName()->toArray(),
            domain: 'planet',
            language: $player->getDaedalus()->getLanguage()
        );
    }

    private function createExamineLog(Player $player, ?LogParameterInterface $actionParameter): RoomLog
    {
        if ($actionParameter instanceof Drone) {
            $logParameters = $this->getDroneLogParameters($actionParameter);

            return $this->createLog(
                $actionParameter->getLogName() . '.examine',
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'items',
                $player,
                $logParameters,
            );
        }

        if ($actionParameter instanceof GameItem) {
            return $this->createLog(
                $actionParameter->getLogName() . '.examine',
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'items',
                $player,
            );
        }

        if ($actionParameter instanceof GameEquipment) {
            $logParameters = $this->getPatrolShipLogParameters($actionParameter);

            return $this->createLog(
                $actionParameter->getLogName() . '.examine',
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'equipments',
                $player,
                $logParameters,
            );
        }

        throw new \LogicException('examine action is not implemented for this type of entity');
    }

    private function getDroneLogParameters(Drone $drone): array
    {
        $upgrades = '';

        if ($drone->isUpgraded()) {
            $language = $drone->getDaedalus()->getLanguage();
            foreach ($drone->getUpgrades() as $upgrade) {
                $upgrades = $upgrades .
                '//' .
                $this->translationService->translate(
                    $upgrade->getName() . '.description',
                    [],
                    'status',
                    $language
                );
            }
        }

        return ['drone_upgrades' => $upgrades];
    }

    private function getPatrolShipLogParameters(GameEquipment $patrolShip): array
    {
        $electricCharges = $patrolShip->getChargeStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $patrolShipArmor = $patrolShip->getChargeStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);

        return [
            'charges' => $electricCharges?->getCharge(),
            'armor' => $patrolShipArmor?->getCharge(),
        ];
    }

    private function getNeronPersonality(Daedalus $daedalus): string
    {
        $neron = $daedalus->getNeron();

        return match (true) {
            $neron->isInhibited() === false => NeronPersonalitiesEnum::UNINHIBITED,
            $this->d100Roll->isSuccessful(Neron::CRAZY_NERON_CHANCE) => NeronPersonalitiesEnum::CRAZY,
            default => NeronPersonalitiesEnum::NEUTRAL,
        };
    }

    private function shouldRevealLog(RoomLog $roomLog, string $visibility): bool
    {
        $player = $roomLog->getPlayerOrThrow();

        return $this->shouldRevealSecretLog($roomLog, $visibility) || $this->shouldRevealCovertLog($player, $visibility);
    }

    private function shouldRevealCovertLog(Player $player, string $visibility): bool
    {
        $place = $player->getPlace();
        $placeHasAFunctionalCamera = $place->hasOperationalEquipmentByName(EquipmentEnum::CAMERA_EQUIPMENT);

        return $visibility === VisibilityEnum::COVERT && $placeHasAFunctionalCamera;
    }

    private function shouldRevealSecretLog(RoomLog $roomLog, string $visibility): bool
    {
        $player = $roomLog->getPlayerOrThrow();
        $place = $player->getPlace();
        $placeHasAWitness = $place->getNumberOfPlayersAlive() > 1;
        $placeHasAFunctionalCamera = $place->hasOperationalEquipmentByName(EquipmentEnum::CAMERA_EQUIPMENT);

        return $visibility === VisibilityEnum::SECRET
        && (
            $placeHasAWitness
            || (
                $placeHasAFunctionalCamera
                && $roomLog->shouldBeRevealedByCamera()
            )
        );
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

    private function parametersToString(array $parameters): string
    {
        if (\array_key_exists('daedalusInfo', $parameters)) {
            $parameters['daedalusInfo'] = $parameters['daedalusInfo']->getId();
        }
        if (\array_key_exists('playerInfo', $parameters)) {
            $parameters['playerInfo'] = $parameters['playerInfo']->getId();
        }

        return json_encode($parameters);
    }
}
