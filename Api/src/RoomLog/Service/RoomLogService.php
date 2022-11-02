<?php

namespace Mush\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogDeclinationEnum;
use Mush\RoomLog\Repository\RoomLogRepository;

class RoomLogService implements RoomLogServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;
    private RoomLogRepository $repository;

    public function __construct(
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService,
        RoomLogRepository $repository,
    ) {
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
        $this->repository = $repository;
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
        }
        if (($equipment = $actionResult->getEquipment()) !== null) {
            $parameters[$equipment->getLogKey()] = $equipment->getLogName();
        }

        return $parameters;
    }

    private function createExamineLog(
        Player $player,
        ?LogParameterInterface $actionParameter,
    ): RoomLog {
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
            return $this->createLog(
                $actionParameter->getLogName() . '.examine',
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'equipments',
                $player,
            );
        }

        throw new \LogicException('examine action is not implemented for this type of entity');
    }

    public function createLog(
        string $logKey,
        Place $place,
        string $visibility,
        string $type,
        ?Player $player = null,
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
            ->setPlace($place)
            ->setPlayer($player)
            ->setVisibility($this->getVisibility($player, $visibility))
            ->setDate($dateTime ?? new \DateTime('now'))
            ->setCycle($place->getDaedalus()->getCycle())
            ->setDay($place->getDaedalus()->getDay())
        ;

        return $this->persist($roomLog);
    }

    private function getVisibility(?Player $player, string $visibility): string
    {
        if ($player === null) {
            return $visibility;
        }

        $place = $player->getPlace();

        $placeEquipements = $place->getEquipments();

        $equipmentIsACamera = fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === EquipmentEnum::CAMERA_EQUIPMENT;

        $equipementIsNotBroken = fn (GameEquipment $gameEquipment) => $gameEquipment->isBroken() === false;

        $placeHasAFunctionalCamera = $placeEquipements->filter($equipmentIsACamera)->filter($equipementIsNotBroken)->count() > 0;
        $placeHasAWitness = $place->getNumberPlayers() > 1;

        if (
            $visibility === VisibilityEnum::SECRET &&
            ($placeHasAWitness ||
             $placeHasAFunctionalCamera)
        ) {
            return VisibilityEnum::REVEALED;
        } elseif (
            $visibility === VisibilityEnum::COVERT &&
            $placeHasAFunctionalCamera
        ) {
            return VisibilityEnum::REVEALED;
        }

        return $visibility;
    }

    public function getRoomLog(Player $player): RoomLogCollection
    {
        return new RoomLogCollection($this->repository->getPlayerRoomLog($player));
    }
}
