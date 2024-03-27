<?php

namespace Mush\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
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
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;

class RoomLogService implements RoomLogServiceInterface
{
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

    public function createLogFromActionResult(
        string $actionName,
        ActionResult $actionResult,
        Player $player,
        ?LogParameterInterface $actionParameter,
        \DateTime $time
    ): ?RoomLog {
        // first lets handle the special case of examine action
        if ($actionName === ActionEnum::EXAMINE) {
            return $this->createExamineLog($player, $actionParameter);
        }

        $logMapping = ActionLogEnum::ACTION_LOGS[$actionName] ?? null;

        if (!$logMapping) {
            return null;
        }

        $actionResultString = $actionResult->getName();
        if (isset($logMapping[$actionResultString])) {
            $logData = $logMapping[$actionResultString];
        } else {
            return null;
        }

        $parameters = $this->getActionLogParameters($actionResult, $player, $actionParameter);

        $visibility = $actionResult->getVisibility();
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
        Player $player = null,
        array $parameters = [],
        \DateTime $dateTime = null
    ): RoomLog {
        // if there is several version of the log
        if (array_key_exists($logKey, $declinations = LogDeclinationEnum::getVersionNumber())) {
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
            ->setVisibility($this->getVisibility($player, $visibility))
            ->setDate($dateTime ?? new \DateTime('now'))
            ->setCycle($place->getDaedalus()->getCycle())
            ->setDay($place->getDaedalus()->getDay())
        ;

        return $this->persist($roomLog);
    }

    public function getRoomLog(Player $player): RoomLogCollection
    {
        return new RoomLogCollection($this->repository->getPlayerRoomLog($player->getPlayerInfo()));
    }

    public function getDaedalusRoomLogs(Daedalus $daedalus): RoomLogCollection
    {
        return new RoomLogCollection($this->repository->getAllRoomLogsByDaedalus($daedalus));
    }

    public function findAllByDaedalusAndPlace(Daedalus $daedalus, Place $place): RoomLogCollection
    {
        return new RoomLogCollection($this->repository->findAllByDaedalusAndPlace($daedalus, $place));
    }

    private function getVisibility(?Player $player, string $visibility): string
    {
        if ($player === null) {
            return $visibility;
        }

        $place = $player->getPlace();
        $placeEquipments = $place->getEquipments();

        $equipmentIsACamera = static fn (GameEquipment $gameEquipment): bool => $gameEquipment->getName() === EquipmentEnum::CAMERA_EQUIPMENT;
        $equipmentIsNotBroken = static fn (GameEquipment $gameEquipment): bool => $gameEquipment->isBroken() === false;

        $placeHasAFunctionalCamera = $placeEquipments->filter($equipmentIsACamera)->filter($equipmentIsNotBroken)->count() > 0;
        $placeHasAWitness = $place->getNumberOfPlayersAlive() > 1;

        if ($visibility === VisibilityEnum::SECRET && ($placeHasAWitness || $placeHasAFunctionalCamera)) {
            return VisibilityEnum::REVEALED;
        }

        if ($visibility === VisibilityEnum::COVERT && $placeHasAFunctionalCamera) {
            return VisibilityEnum::REVEALED;
        }

        return $visibility;
    }

    private function getActionLogParameters(
        ActionResult $actionResult,
        Player $player,
        ?LogParameterInterface $actionParameter
    ): array {
        $parameters = [];
        $parameters[$player->getLogKey()] = $player->getLogName();

        if (($quantity = $actionResult->getQuantity()) !== null) {
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
        if (($equipment = $actionResult->getEquipment()) !== null) {
            $parameters[$equipment->getLogKey()] = $equipment->getLogName();
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

    public function getNumberOfUnreadRoomLogsForPlayer(Player $player): int
    {
        return $this->getRoomLog($player)->filter(
            static fn (RoomLog $roomLog) => $roomLog->isUnreadBy($player)
        )->count();
    }

    public function markRoomLogAsReadForPlayer(RoomLog $roomLog, Player $player): void
    {
        $roomLog
          ->addReader($player)
          ->cancelTimestampable(); // We don't want to update the updatedAt field when player reads the log because this would change the order of the messages
        $this->entityManager->persist($roomLog);
        $this->entityManager->flush();
    }

    public function markAllRoomLogsAsReadForPlayer(Player $player): void
    {
        $roomLogs = $this->getRoomLog($player);

        foreach ($roomLogs as $roomLog) {
            $roomLog
              ->addReader($player)
              ->cancelTimestampable();
            $this->entityManager->persist($roomLog);
        }

        $this->entityManager->flush();
    }

    private function getPatrolShipLogParameters(GameEquipment $patrolShip): array
    {
        /** @var ChargeStatus|null $electricCharges * */
        $electricCharges = $patrolShip->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);
        /** @var ChargeStatus|null $patrolShipArmor * */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);

        return [
            'charges' => $electricCharges?->getCharge(),
            'armor' => $patrolShipArmor?->getCharge(),
        ];
    }
}
