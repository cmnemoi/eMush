<?php

namespace Mush\RoomLog\Service;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Communication\Enum\NeronPersonalitiesEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogDeclinationEnum;
use Mush\RoomLog\Repository\RoomLogRepository;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class RoomLogService implements RoomLogServiceInterface
{
    public const int OBSERVANT_REVEAL_CHANCE = 25;

    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;
    private RoomLogRepository $repository;
    private TranslationServiceInterface $translationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService,
        RoomLogRepository $repository,
        TranslationServiceInterface $translationService
    ) {
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
        $this->repository = $repository;
        $this->translationService = $translationService;
    }

    public function persist(RoomLog $roomLog): RoomLog
    {
        $this->entityManager->persist($roomLog);
        $this->entityManager->flush();

        return $roomLog;
    }

    public function findById(int $id): ?RoomLog
    {
        $roomLog = $this->repository->find($id);

        return $roomLog instanceof RoomLog ? $roomLog : null;
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
                $parameters[$keyVersion] = $this->randomService->random(1, $versionNb);
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
            ->setVisibility($this->getVisibility($player, $visibility))
            ->setCreatedAt($dateTime ?? new \DateTime('now'))
            ->setCycle($place->getDaedalus()->getCycle())
            ->setDay($place->getDaedalus()->getDay());

        return $this->persist($roomLog);
    }

    public function getRoomLog(Player $player): RoomLogCollection
    {
        $dateLimit = $player->hasSkill(SkillEnum::TRACKER) ? new \DateTime('-2 days') : new \DateTime('-1 day');

        return new RoomLogCollection($this->repository->getPlayerRoomLog($player->getPlayerInfo(), $dateLimit));
    }

    public function getDaedalusRoomLogs(Daedalus $daedalus): RoomLogCollection
    {
        return new RoomLogCollection($this->repository->getAllRoomLogsByDaedalus($daedalus));
    }

    public function findAllByDaedalusAndPlace(Daedalus $daedalus, Place $place): RoomLogCollection
    {
        return new RoomLogCollection($this->repository->findAllByDaedalusAndPlace($daedalus, $place));
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
            $roomLog
                ->addReader($player)
                ->cancelTimestampable(); // We don't want to update the updatedAt field when player reads the log because this would change the order of the messages

            $this->entityManager->persist($roomLog);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            // ignore as this is probably due to a race condition
        }
    }

    public function markAllRoomLogsAsReadForPlayer(Player $player): void
    {
        $unreadLogs = $this->getRoomLog($player)->filter(
            static fn (RoomLog $roomLog) => $roomLog->isUnreadBy($player)
        );

        foreach ($unreadLogs as $roomLog) {
            $this->markRoomLogAsReadForPlayer($roomLog, $player);
        }
    }

    public function findOneByOrThrow(array $parameters): RoomLog
    {
        return $this->repository->findOneBy($parameters) ?? throw new \RuntimeException("Log {$parameters['log']} not found in daedalus {$parameters['place']->getDaedalus()->getId()} for given parameters");
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
        
        if ($visibility === VisibilityEnum::COVERT && $player->hasStatus(PlayerStatusEnum::PARIAH)) {
            $visibility = VisibilityEnum::SECRET;
        }

        if ($this->shouldRevealSecretLog($player, $visibility) || $this->shouldRevealCovertLog($player, $visibility)) {
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

            // we need to translate planet name before logging it, as it is saved in database as an array of numbers (basically)
            if (str_contains($key, 'planet')) {
                /** @var Planet $planet */
                $planet = $actionParameter;

                $parameters[$key] = $this->translationService->translate(
                    key: 'planet_name',
                    parameters: $planet->getName()->toArray(),
                    domain: 'planet',
                    language: $player->getDaedalus()->getLanguage()
                );
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

    private function createExamineLog(Player $player, ?LogParameterInterface $actionParameter): RoomLog
    {
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

    private function getPatrolShipLogParameters(GameEquipment $patrolShip): array
    {
        /** @var null|ChargeStatus $electricCharges * */
        $electricCharges = $patrolShip->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);

        /** @var null|ChargeStatus $patrolShipArmor * */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);

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
            $this->randomService->randomPercent() <= Neron::CRAZY_NERON_CHANCE => NeronPersonalitiesEnum::CRAZY,
            default => NeronPersonalitiesEnum::NEUTRAL,
        };
    }

    private function shouldRevealCovertLog(Player $player, string $visibility): bool
    {
        $place = $player->getPlace();
        $placeHasAFunctionalCamera = $place->hasOperationalEquipmentByName(EquipmentEnum::CAMERA_EQUIPMENT);
        $observantRevealsLog = $player->getAlivePlayersInRoomExceptSelf()->getPlayersWithSkill(SkillEnum::OBSERVANT)->count() > 0 && $this->randomService->isSuccessful(self::OBSERVANT_REVEAL_CHANCE);

        return $visibility === VisibilityEnum::COVERT && ($placeHasAFunctionalCamera || $observantRevealsLog);
    }

    private function shouldRevealSecretLog(Player $player, string $visibility): bool
    {
        $place = $player->getPlace();
        $placeHasAWitness = $place->getNumberOfPlayersAlive() > 1;
        $placeHasAFunctionalCamera = $place->hasOperationalEquipmentByName(EquipmentEnum::CAMERA_EQUIPMENT);

        return $visibility === VisibilityEnum::SECRET && ($placeHasAWitness || $placeHasAFunctionalCamera);
    }
}
